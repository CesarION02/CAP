<?php namespace App\SData;

use App\Http\Controllers\prePayrollController;
use App\Models\commentsControl;
use App\SData\SOverJourneyCore;
use App\SUtils\SDateTimeUtils;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SPrepayrollAdjustUtils;
use App\SUtils\SRegistryRow;
use App\SUtils\SReportsUtils;
use Carbon\Carbon;
use App\SUtils\SDateUtils;
use App\SUtils\Logger\SLogger;

class SDataProcess {

    /**
     * var_dump(Carbon::SUNDAY);     // int(0)
     * var_dump(Carbon::MONDAY);     // int(1)
     * var_dump(Carbon::TUESDAY);    // int(2)
     * var_dump(Carbon::WEDNESDAY);  // int(3)
     * var_dump(Carbon::THURSDAY);   // int(4)
     * var_dump(Carbon::FRIDAY);     // int(5)
     * var_dump(Carbon::SATURDAY);   // int(6)
     * 
     * "maxGapMinutes": (minutos), minutos para búsqueda de checadas un día antes o después, no solo por el tipo de checada.
     * "maxGapSchedule": (minutos), minutos de holgura para "encajonar" checadas en un horario en la función determineSchedule().
     *                              También se usa para determinar si las checadas fueron de entrada o salida en los procesos iniciales.
     * "maxGapCheckSchedule": (minutos), minutos tomados en cuenta para poner la leyenda "revisar horario".
     */

     /**
      * Realiza el proceso de empatar checadas vs horarios programados y regresa una
      * lista de SRegistryRow con los datos correspondientes
      *
      * @param string $sStartDate
      * @param string $sEndDate
      * @param int $payWay [ 1: QUINCENA, 2: SEMANA, 0: TODOS]
      * @param int $tReport [\SCons::REP_DELAY, \SCons::REP_HR_EX]
      * @param array $lEmployees arreglo de ids de empleados

      * @return [SRegistryRow] (array)
      */
    public static function process($sStartDate, $sEndDate, $payWay, $lEmployees)
    {
        /**
         * Guarda un objeto de la clase Logger, con esto durante el proceso se almacenarán 
         * los logs generados en el proceso de prenómina.
         */
        $oLogger = new SLogger($sStartDate, $sEndDate, $payWay);
        session(['logger' => $oLogger]);

        SDataProcess::checkEvents();
        $comments = commentsControl::where('is_delete',0)->select('key_code','value')->get();

        // Filtrar empleados, solo aparecerán aquellos que hayan sido dados de alta antes de la fecha de inicio
        $lEmployees = SReportsUtils::filterEmployeesByAdmissionDate($lEmployees, $sEndDate, 'id');

        $data53 = SDataProcess::getSchedulesAndChecks($sStartDate, $sEndDate, $payWay, $lEmployees, $comments);

        $aEmployees = $lEmployees->pluck('id');
        $lWorkshifts = SDelayReportUtils::getWorkshifts($sStartDate, $sEndDate, $payWay, $aEmployees);
        // Rutina para verificación de renglones completos
        $lDataComplete = SDataProcess::completeDays($sStartDate, $sEndDate, $data53, $aEmployees, $lWorkshifts, $comments);
        $lData53_2 = SDataProcess::addEventsDaysOffAndHolidays($lDataComplete, $lWorkshifts, $comments);
        
        $aEmployeeBen = $lEmployees->pluck('ben_pol_id', 'id');
        $lDataWithAbs = SDataProcess::addAbsences($lData53_2, $aEmployeeBen, $comments);

        // $aEmployeeOverTime = $lEmployees->pluck('is_overtime', 'id');
        $aEmployeeOverTime = $lEmployees->pluck('policy_extratime_id', 'id');
        $lData = SDataProcess::addDelaysAndOverTime($lDataWithAbs, $aEmployeeOverTime, $sEndDate, $comments);

        $lDataWSun = SDataProcess::addSundayPay($lData);

        // Se comenta este método ya que se cambió el procesamiento para poder hacer ajustes
        // $lDataJ = SOverJourneyCore::overtimeByIncompleteJourney($sStartDate, $sEndDate, $lDataWSun, $aEmployeeOverTime);
        $lAllData = SOverJourneyCore::processOverTimeByOverJourney($lDataWSun, $sStartDate, $comments);

        $lAllData = SDataProcess::putAdjustInRows($sStartDate, $sEndDate, $lAllData);

        /**
         * Remueve el objeto logger de la sesión actual
         */
        session()->forget('logger');

        return $lAllData;
    }

    /**
     * Obtiene horarios y checadas, retorna una lista de objetos con los datos correspondientes
     *
     * @param string $sStartDate
     * @param string $sEndDate
     * @param int $payWay
     * @param collection $lEmployees
     * 
     * @return array SRegistryRow
     */
    public static function getSchedulesAndChecks($sStartDate, $sEndDate, $payWay, $lEmployees, $comments = null)
    {
        $aEmployees = $lEmployees->pluck('id');
        $aRegistries = SDelayReportUtils::getRegistries($sStartDate, $sEndDate, $payWay, $aEmployees, true);
        $lWorkshifts = SDelayReportUtils::getWorkshifts($sStartDate, $sEndDate, $payWay, $aEmployees);

        $aDates = [];
        $oStartDate = Carbon::parse($sStartDate);
        $oEndDate = Carbon::parse($sEndDate);
        $oDate = clone $oStartDate;

        /**
         * crea un arreglo con los días a consultar
         */
        while ($oDate->lessThanOrEqualTo($oEndDate)) {
            $aDates[] = $oDate->toDateString();
            $oDate->addDay();
        }

        $cRegistries = collect($aRegistries);
        $lRows = array();
        $idEmployee = 0;
        $isNew = true;
        
        foreach ($lEmployees as $oEmployee) {
            $newRow = null;
            $idEmployee = $oEmployee->id;
            
            foreach ($aDates as $sDate) {

                $registries = clone $cRegistries;
                $registries = $registries->where('date', $sDate)
                                        ->where('employee_id', $idEmployee);

                //filtrar checadas repetidas
                $registries = SDataProcess::manageCheks($registries, $sDate);

                $bug = false;
                if (sizeof($registries) == 1) {
                    $res = SDataProcess::manageOneCheck($sDate, $idEmployee, $registries, $lWorkshifts, $sEndDate);
                    
                    if (is_array($res)) {
                        $registries = $res[1];
                    }

                    if ($res == 2) {
                        $regTemp = $registries;
                        $bug = true;
                        $registries = [];
                    }
                    else if ($res == 3) {
                        $registries = [];
                    }
                }
                toAbsences:
                if (sizeof($registries) > 0) {
                    foreach ($registries as $registry) {
                        $qWorkshifts = clone $lWorkshifts;
                        $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, clone $qWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);
        
                        $isNew = $theRow[0];
                        $newRow = $theRow[1];
                        $again = $theRow[2];
                        $fRegistry = $theRow[3];
        
                        if ($isNew) {
                            $lRows[] = $newRow;
                        }

                        if ($again) {
                            if ($fRegistry != null) {
                                $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $fRegistry, clone $lWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);
                            }
                            else {
                                $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, clone $lWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);
                            }
                            $isNew = $theRow[0];
                            $newRow = $theRow[1];
            
                            if ($isNew) {
                                $lRows[] = $newRow;
                            }
                        }
                    }
                }
                else {
                    //si no es nuevo lo procesamos con el renglón actual
                    if (! $isNew) {
                        $registry = (object) [
                            'date' => $sDate,
                            'time' => '12:00:00',
                            'type_id' => 1,
                            'to_close' => true,
                            'is_modified' => false
                        ];

                        $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, clone $lWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);

                        $isNew = $theRow[0];
                        $newRow = $theRow[1];

                        if ($isNew) {
                            $lRows[] = $newRow;
                        }
                    }

                    if ($newRow != null && $sDate == $newRow->outDate) {
                        continue;
                    }

                    $registry = (object) [
                                    'date' => $sDate,
                                    'time' => '12:00:00',
                                    'is_modified' => false
                                ];
                                
                    $result = SDelayReportUtils::getSchedule($sDate, $sDate, $idEmployee, $registry, clone $lWorkshifts, \SCons::REP_HR_EX);

                    //Revisa si el turno actual es nocturno
                    $isNight = false;
                    $scheduleText = null;
                    $dayBefore = null;
                    if (!is_null($result)) {
                        if (!is_null($result->auxWorkshift)) {
                            $oAuxSchedule = $result->auxWorkshift;
                            $isNight = $oAuxSchedule->is_night != null ? $oAuxSchedule->is_night : false;
                            $chekHourEntry = Carbon::parse($oAuxSchedule->entry)->subHour();
                            $scheduleName = $oAuxSchedule->name;
                        }
                        else if (!is_null($oAuxSchedule = $result->auxScheduleDay)) {
                            $oAuxSchedule = $result->auxScheduleDay;
                            $isNight = $oAuxSchedule->is_night != null ? $oAuxSchedule->is_night : false;
                            $chekHourEntry = Carbon::parse($oAuxSchedule->entry)->subHour();
                            $scheduleName = $oAuxSchedule->template_name;
                        }
                    }

                    //si el turno es nocturno busca entrada un dia anterior (solo si el dia del renglon es igual a la fecha de inicio)
                    if ($isNight) {
                        $dayBefore = SDataProcess::checkDayBefore($sStartDate, $sDate, $idEmployee, $payWay, $chekHourEntry->toTimeString());
                        if (!is_null($dayBefore)) {
                            $qWorkshifts = clone $lWorkshifts;
                            $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $dayBefore, clone $qWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);
                            $newRow = $theRow[1];
                            $newRow->outDateTime = $sDate;
                            $newRow->hasCheckOut = false;
                            $newRow->comments = $newRow->comments."Sin salida. ";
                            $newRow->isDayChecked = true;
                            $newRow->scheduleText = strtoupper($scheduleName);
                            $lRows[] = $newRow;
                        }
                    }

                    if (is_null($dayBefore)) {

                        $otherRow = new SRegistryRow();
                        $otherRow->idEmployee = $idEmployee;
                        $otherRow->numEmployee = $oEmployee->num_employee;
                        $otherRow->employeeAreaId = $oEmployee->employee_area_id;
                        $otherRow->employee = $oEmployee->name;
                        $otherRow->external_id = $oEmployee->external_id;
                        $otherRow->overtimeCheckPolicy = $oEmployee->policy_extratime_id;
    
                        $otherRow = SDataProcess::setDates($result, $otherRow, $sDate, $comments);
    
                        $otherRow->hasChecks = false;
                        $otherRow->hasCheckOut = false;
                        $otherRow->hasCheckIn = false;
                        if ($otherRow->workable) {
                            $otherRow->comments = $otherRow->comments."Sin checadas. ";
                            if ($comments != null) {
                                if ($comments->where('key_code','hasChecks')->first()['value'] ||
                                    $comments->where('key_code','hasCheckIn')->first()['value'] ||
                                    $comments->where('key_code','hasCheckOut')->first()['value']
                                ) {
                                    $otherRow->isDayChecked = true;
                                }
                            }
                        }
    
                        $otherRow->inDateTime = $sDate;
                        $otherRow->outDateTime = $sDate;
    
                        $lRows[] = $otherRow;
    
                        if ($bug) {
                            $registries = $regTemp;
                            goto toAbsences;
                        }
                    }
                }
            }

            if (! $isNew) {
                if ($newRow->inDate == $sEndDate) {
                    $registry = (object) [
                        'type_id' => \SCons::REG_IN,
                        'time' => '12:00:00',
                        'date' => $newRow->inDate,
                        'employee_id' => $idEmployee,
                        'is_modified' => false
                    ];

                    $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, clone $lWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);
                    $isNew = [0];
                    $newRow = $theRow[1];
                    $again = $theRow[2];
                    $fRegistry = $theRow[3];
    
                    if (isset($theRow[501]) && $theRow[501]) {
                        $newRow = null;
                        $isNew = true;
                        $again = false;

                        continue;
                    }
                    if ($isNew) {
                        $lRows[] = $newRow;
                        $isNew = false;
                    }

                    if ($again) {
                        if ($fRegistry != null) {
                            $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $fRegistry, clone $lWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);
                        }
                        else {
                            $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, clone $lWorkshifts, $sStartDate, $sEndDate, $comments, $payWay);
                        }

                        $isNew = $theRow[0];
                        $newRow = $theRow[1];
        
                        if ($isNew) {
                            $lRows[] = $newRow;
                        }
                    }
                }

                $isNew = true;
                $newRow = null;
            }
        }

        return $lRows;
    }

   /**
     * Procesa el renglón de checada y busca si tiene un horario asignado, esta función es usada para 
     * el reporte de horas extras, ya que consulta sobre el registro de salida
     *
     * @param SRegistryRow $newRow
     * @param boolean $isNew
     * @param int $idEmployee
     * @param query_result $registry
     * @param query_assigns $lAssigns
     * @param query $qWorkshifts
     * 
     * @return array $response[0] = boolean que determina si el renglón está listo para ser agregado
     *               $response[1] = SRegistryRow que puede ser procesado de nuevo o estar completo
     *               $response[2] = boolean que determina si el renglón será reprocesado, esto cuando falta un registro de entrada o salida
     */
    private static function manageRow($newRow, $isNew, $idEmployee, $registry, $qWorkshifts, $sStartDate, $sEndDate, $comments = null, $payWay = null)
    {
        if ($isNew) {
            $newRow = new SRegistryRow();
            $newRow->idEmployee = $idEmployee;
            $newRow->numEmployee = $registry->num_employee;
            $newRow->employeeAreaId = $registry->employee_area_id;
            $newRow->employee = $registry->name;
            $newRow->external_id = $registry->external_id;
            $newRow->overtimeCheckPolicy = $registry->policy_extratime_id;
        }
        
        $again = false;
        $oFoundRegistry = null;
        $isOut = false;

        if ($registry->type_id == \SCons::REG_OUT) {
            if (! $isNew) {
                $b24Hr = SDataProcess::gapInAndOut24Hr($newRow->inDateTime, $registry->date.' '.$registry->time);
                if ($b24Hr) {
                    $newRow->outDate = $newRow->inDate;
                    $newRow->outDateTime = $newRow->inDate;
                    $newRow->comments = $newRow->comments."Sin salida. ";
                    if ($comments != null) {
                        if ($comments->where('key_code','hasCheckOut')->first()['value']) {
                            $newRow->isDayChecked = true;
                        }
                    }
                    $newRow->hasCheckOut = false;

                    $response = array();
                    $isNew = true;
                    $response[] = $isNew;
                    $response[] = $newRow;
                    $again = true;
                    $response[] = $again;
                    $oFoundRegistry = null;
                    $response[] = $oFoundRegistry;

                    if ($isOut) {
                        $response[501] = true;
                    }

                    return $response;
                }
            }

            $result = SDelayReportUtils::getSchedule($sStartDate, $sEndDate, $idEmployee, $registry, clone $qWorkshifts, \SCons::REP_HR_EX);

            // no tiene horario para el día actual
            if ($result == null) {
                if ($isNew) {
                    $bFound = false;
                    if ($registry->date == $sStartDate) {
                        // buscar entrada un día antes
                        $oFoundRegistryI = null;
                        $oDateAux = Carbon::parse($registry->date);
                        $oDateAux->subDay();
                        $oAux = null;
                        $entry = "";
                        if ($oAux != null) {
                            $entry = $oAux->entry;
                        }
                        
                        $oFoundRegistryI = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_IN, $entry);

                        if ($oFoundRegistryI != null) {
                            $newRow->sInDate = $oFoundRegistryI->date.' '.$oFoundRegistryI->time;
                            $newRow->inDate = $oFoundRegistryI->date;
                            $newRow->inDateTime = $oFoundRegistryI->date.' '.$oFoundRegistryI->time;
                            $newRow->isModifiedIn = $oFoundRegistryI->is_modified;
                            
                            $bFound = true;
                        }
                    }

                    if (! $bFound) {
                        $newRow->inDate = $registry->date;
                        $newRow->inDateTime = $registry->date;
                        $newRow->isModifiedIn = $registry->is_modified;
                        $date = $newRow->inDate;
                        $time = null;
                        $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 1, \SCons::PP_TYPES['JE'], $newRow->idEmployee);
                
                        if (count($adjs) == 0) {
                            $newRow->hasCheckIn = false;
                            $newRow->comments = $newRow->comments."Sin entrada. ";
                            if ($comments != null) {
                                if ($comments->where('key_code','hasCheckIn')->first()['value']) {
                                    $newRow->isDayChecked = true;
                                }
                            }
                        }
                    }

                    $isNew = false;
                    $again = true;
                }
                else {
                    $otherResult = SDelayReportUtils::getNearSchedule($registry->date, $registry->time, $idEmployee, clone $qWorkshifts);
                    $isNew = true;

                    if ($otherResult != null) {
                        $oAux = null;
                        if ($otherResult->oAuxDate != null) {
                            $otherResult->variableDateTime = $otherResult->oAuxDate;

                            if ($otherResult->auxWorkshift != null) {
                                $oAux = $otherResult->auxWorkshift;
                            }
                            else {
                                if ($otherResult->auxScheduleDay != null) {
                                    $oAux = $otherResult->auxScheduleDay;
                                }
                            }

                            if ($oAux != null) {
                                $otherResult->pinnedDateTime = Carbon::parse($otherResult->oAuxDate->toDateString()." ".$oAux->departure);
                            }
                        }

                        $newRow = SDataProcess::setDates($otherResult, $newRow, null, $comments);
                    }
                    else {
                        $newRow->outDate = $registry->date;
                        $newRow->outDateTime = $registry->date.' '.$registry->time;
                        $newRow->comments = $newRow->comments."Sin horario. ";
                        if ($comments != null) {
                            if ($comments->where('key_code','hasSchedule')->first()['value']) {
                                $newRow->isDayChecked = true;
                            }
                        }
                        $newRow->hasSchedule = false;
                    }
                }
            }
            else {
                if ($newRow->inDate != null) {
                    if ($newRow->outDate == null) {
                        $newRow = SDataProcess::setDates($result, $newRow, null, $comments);

                        if (isset($registry->to_close) && $registry->to_close) {
                            $newRow->outDate = $newRow->inDate;
                            $newRow->outDateTime = $newRow->inDate;
                            $date = $newRow->outDate;
                            $time = null;
                            $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['JS'], $newRow->idEmployee);
                    
                            if (count($adjs) == 0) {
                                $newRow->comments = $newRow->comments."Sin salida. ";
                                if($comments != null) {
                                    if($comments->where('key_code','hasCheckOut')->first()['value']) {
                                        $newRow->isDayChecked = true;
                                    }
                                }
                                $newRow->hasCheckOut = false;
                            }
                        }
    
                        $isNew = true;
                    }
                }
                else {
                    //Sin entrada
                    $bFound = false;
                    $oAux = null;
                    if ($result->auxWorkshift != null) {
                        $oAux = $result->auxWorkshift;
                    }
                    else {
                        if ($result->auxScheduleDay != null) {
                            $oAux = $result->auxScheduleDay;
                        }
                    }
                    if ($result->pinnedDateTime->toDateString() == $sStartDate && $oAux != null && $oAux->is_night) {
                        // buscar entrada un día antes
                        $oFoundRegistryI = null;
                        $oDateAux = clone $result->pinnedDateTime;
                        $oDateAux->subDay();
                        $entry = "";
                        if ($oAux != null) {
                            $entry = $oAux->entry;
                        }
                        $oFoundRegistryI = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_IN, $entry);

                        if ($oFoundRegistryI != null) {
                            $newRow->sInDate = $oFoundRegistryI->date.' '.$oFoundRegistryI->time;
                            $newRow->inDate = $oFoundRegistryI->date;
                            $newRow->inDateTime = $oFoundRegistryI->date.' '.$oFoundRegistryI->time;
                            $newRow->isModifiedIn = $oFoundRegistryI->is_modified;
    
                            $isNew = false;
                            $again = true;
                            $bFound = true;
                        }
                    }

                    if (! $bFound) {
                        $newRow->outDate = $result->variableDateTime->toDateString();
                        $newRow->outDateTime = $result->variableDateTime->format('Y-m-d H:i:s');
                        $newRow->outDateTimeSch = $result->pinnedDateTime->format('Y-m-d H:i:s');
                        $newRow->isModifiedOut = isset($result->registry->is_modified) ? $result->registry->is_modified : false;

                        if(!is_null($result->auxScheduleDay)){
                            $newRow->scheduleText = strtoupper($result->auxScheduleDay->template_name);
                        }else if(!is_null($result->auxWorkshift)){
                            $newRow->scheduleText = strtoupper($result->auxWorkshift->name);
                        }

                        $newRow->cutId = SDelayReportUtils::getCutId($result);
                        $newRow->overtimeCheckPolicy = SDelayReportUtils::getOvertimePolicy($result);

                        $isNew = true;
                        $again = false;
                        $newRow->inDate = $registry->date;
                        $newRow->inDateTime = $registry->date;
                        $date = $newRow->inDate;
                        $time = null;
                        $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 1, \SCons::PP_TYPES['JE'], $newRow->idEmployee);
                
                        if (count($adjs) == 0) {
                            $newRow->hasCheckIn = false;
                            $newRow->comments = $newRow->comments."Sin entrada. ";
                            if($comments != null) {
                                if($comments->where('key_code','hasCheckIn')->first()['value']) {
                                    $newRow->isDayChecked = true;
                                }
                            }
                        }
                    }
                }
            }

        }
        else {
            if ($newRow->outDate == null) {
                if ($newRow->inDate == null) {
                    $newRow->sInDate = $registry->date.' '.$registry->time;
                    $newRow->inDate = $registry->date;
                    $newRow->inDateTime = $registry->date.' '.$registry->time;
                    $newRow->isModifiedIn = $registry->is_modified;

                    $isNew = false;
                }
                else {
                    // Sin salida
                    $bFound = false;
                    $bAfterDay = false;

                    $dateCut = null;
                    switch ($payWay) {
                        case \SCons::PAY_W_Q:
                            $dateCut =  \DB::table('hrs_prepay_cut')
                                            ->where([['dt_cut', $newRow->inDate], ['is_delete', 0]])
                                            ->value('dt_cut');
                            break;
                        case \SCons::PAY_W_S:
                            $dateCut =  \DB::table('week_cut')
                                            ->where('fin', $newRow->inDate)
                                            ->value('fin');
                            break;
                        default:
                            break;
                    }

                    //Si el ultimo renglon tiene una entrada correspondiente al dia siguiente (turno nocturno) lo elimina.
                    if ($newRow->inDate == $sEndDate) {
                        $isNight = false;
                        $oDate = Carbon::parse($sEndDate);
                        $oDate->addDay();
                        $dateTime = Carbon::parse($newRow->inDateTime);
                        $auxRegistry = $registry;
                        $auxRegistry->date = $oDate->format('Y-m-d');
                        $oSchedule = SDelayReportUtils::getSchedule($oDate->format('Y-m-d'), $oDate->format('Y-m-d'), $idEmployee, $auxRegistry , clone $qWorkshifts, \SCons::REP_HR_EX);
    
                        if (!is_null($oSchedule)) {
                            if (!is_null($oSchedule->auxWorkshift)) {
                                $oAuxSchedule = $oSchedule->auxWorkshift;
                                $isNight = $oAuxSchedule->is_night != null ? $oAuxSchedule->is_night : false;
                                $chekHourEntry = (Carbon::parse($oAuxSchedule->entry)->hour) - 1;
                            }
                            else if (!is_null($oAuxSchedule = $oSchedule->auxScheduleDay)) {
                                $oAuxSchedule = $oSchedule->auxScheduleDay;
                                $isNight = $oAuxSchedule->is_night != null ? $oAuxSchedule->is_night : false;
                                $chekHourEntry = (Carbon::parse($oAuxSchedule->entry)->hour) - 1;
                            }
                        }
    
                        if (!is_null($dateCut) && $isNight) {
                            if ($dateTime->hour >= $chekHourEntry) {
                                $isOut = true;
                            }
                        }
                    }

                    if ($newRow->inDate == $sEndDate) {
                        // buscar salida un día después
                        $oDateAux = Carbon::parse($registry->date);
                        $oDateAux->addDay();
                        $oFoundRegistry = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_OUT);
                        
                        if ($oFoundRegistry != null) {
                            $bAfterDay = true;
                            if ($oFoundRegistry->date == $oDateAux->toDateString()) {
                                // $config = \App\SUtils\SConfiguration::getConfigurations();

                                // $registryAux = (object) [
                                //     'type_id' => \SCons::REG_IN,
                                //     'time' => $oFoundRegistry->time,
                                //     'date' => $oFoundRegistry->date,
                                //     'employee_id' => $idEmployee
                                // ];

                                // $sched = SDelayReportUtils::getSchedule($oDateAux->toDateString(), $oDateAux->toDateString(), $idEmployee, $registryAux, clone $qWorkshifts, \SCons::REP_DELAY);
                                // if ($sched != null && abs($sched->diffMinutes) <= $config->maxGapMinutes) {
                                    $isOut = true;
                                // }
                            }

                            $isNew = false;
                            $bFound = true;
                            $again = true;
                        }
                    }

                    if (! $bFound) {
                        $result = SDelayReportUtils::getSchedule($sStartDate, $sEndDate, $idEmployee, $registry, clone $qWorkshifts, \SCons::REP_HR_EX);

                        if ($result != null) {
                            // $newRow->inDate = $result->variableDateTime->toDateString();
                            // $newRow->inDateTime = $result->variableDateTime->toDateTimeString();
                            $night = false;
                            if ($result->auxScheduleDay != null) {
                                $oAux = $result->auxScheduleDay;
                                $night = $oAux->is_night;
                            }
                            else {
                                if ($result->auxWorkshift != null) {
                                    $oAux = $result->auxWorkshift;
                                    if ($oAux->is_night) {
                                        $night = true;
                                    }
                                }
                                else {
                                    return 0;
                                }
                            }
                            if ((! $night && $bAfterDay) || (! $night && $newRow->inDate < $registry->date)) {
                                $result->pinnedDateTime->subDay();
                            }

                            $newRow->outDateTimeSch = $result->pinnedDateTime->toDateTimeString();
                            $newRow->outDate = $result->pinnedDateTime->toDateString();
                            $newRow->outDateTime = $result->pinnedDateTime->toDateString();
                            $newRow->isModifiedOut = $result->registry->is_modified;

                            $newRow->isSpecialSchedule = $result->auxIsSpecialSchedule;
                            if ($newRow->isSpecialSchedule) {
                                $newRow->others = $newRow->others."Turno especial (".$result->auxWorkshift->name."). ";
                                if($comments != null) {
                                    if($comments->where('key_code','isSpecialSchedule')->first()['value']) {
                                        $newRow->isDayChecked = true;
                                    }
                                }
                            }
                        }
                        else {
                            $newRow->outDate = $newRow->inDate;
                            $newRow->outDateTime = $newRow->inDate;
                        }

                        $date = $newRow->outDate;
                        $time = null;
                        $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['JS'], $newRow->idEmployee);
                
                        if (count($adjs) == 0) {
                            $newRow->comments = $newRow->comments."Sin salida. ";
                            if($comments != null) {
                                if($comments->where('key_code','hasCheckOut')->first()['value']) {
                                    $newRow->isDayChecked = true;
                                }
                            }
                            $newRow->hasCheckOut = false;
                        }
                        $again = true;
                        $isNew = true;
                    }
                }
            }
        }

        $response = array();
        $response[] = $isNew;
        $response[] = $newRow;
        $response[] = $again;
        $response[] = $oFoundRegistry;

        if ($isOut) {
            $response[501] = true;
        }

        return $response;
    }

    /**
     * Asigna los datos correspondientes a las fechas de entrada, salida,
     * horas extras, no laborable y sin horario.
     *
     * @param \App\SUtils\SDateComparison $result
     * @param Object $oRow
     * @param string $sDate
     * 
     * @return Object $oRow
     */
    private static function setDates($result = null, $oRow = null, $sDate = null, $comments = null)
    {
        if ($result == null) {
            $oRow->outDate = $sDate;
            $oRow->inDate = $sDate;
            // $oRow->inDateTime = $sDate.' 00:00:00';
            // $oRow->outDateTime = $sDate.' 00:00:00';
            $oRow->inDateTime = $sDate;
            $oRow->outDateTime = $sDate;

            $oRow->comments = $oRow->comments."Sin horario. ";
            if ($comments != null) {
                if ($comments->where('key_code','hasSchedule')->first()['value']) {
                    $oRow->isDayChecked = true;
                }
            }
            $oRow->hasSchedule = false;
        }
        else {
            $oRow->isSpecialSchedule = $result->auxIsSpecialSchedule;
            if ($oRow->isSpecialSchedule) {
                $oRow->others = $oRow->others."Turno especial (".$result->auxWorkshift->name."). ";
                if ($comments != null) {
                    if ($comments->where('key_code','isSpecialSchedule')->first()['value']) {
                        $oRow->isDayChecked = true;
                    }
                }
            }

            $oRow->scheduleFrom = SDataProcess::getOrigin($result);

            if ($oRow->scheduleFrom == \SCons::FROM_ASSIGN) {
                $oRow->scheduleText = strtoupper($result->auxScheduleDay->template_name);
            }
            else {
                $oRow->scheduleText = strtoupper($result->auxWorkshift->name);
                if ($result->withRegistry) {
                    $oRow->workJourneyMins = $result->auxWorkshift->work_time * 60;
                }
            }

            if ($oRow->scheduleFrom == \SCons::FROM_ASSIGN && ! $result->auxScheduleDay->is_active) {
                if ($result->withRegistry) {
                    $oRow->outDate = $result->variableDateTime->toDateString();
                    $oRow->outDateTime = $result->variableDateTime->toDateTimeString();
                    $oRow->isModifiedOut = isset($result->registry->is_modified) ? $result->registry->is_modified : false;
                }
                $oRow->comments = $oRow->comments."No laborable. ";
                if ($comments != null) {
                    if ($comments->where('key_code','workable')->first()['value']) {
                        $oRow->isDayChecked = true;
                    }
                }
                $oRow->workable = false;

                return $oRow;
            }

            $sInSchedule = SDelayReportUtils::getScheduleIn($result, $oRow->inDateTime);
            $oRow->inDateTimeSch = $sInSchedule;

            if (! SDelayReportUtils::isNight($result) && $oRow->inDateTime != null && 
                ($result->withRegistry && $result->variableDateTime->toDateString() != $oRow->inDateTime)) {
                $oRow->outDateTimeSch = $oRow->inDate.' '.$result->pinnedDateTime->toTimeString();
            }
            else {
                $oRow->outDateTimeSch = $result->pinnedDateTime->toDateTimeString();
            }
            
            if ($result->withRegistry) {
                $oRow->outDate = $result->variableDateTime->toDateString();
                $oRow->outDateTime = $result->variableDateTime->toDateTimeString();
                $oRow->isModifiedOut = isset($result->registry->is_modified) ? $result->registry->is_modified : false;
            }
    
            $oRow->cutId = SDelayReportUtils::getCutId($result);
            $oRow->overtimeCheckPolicy = SDelayReportUtils::getOvertimePolicy($result);
            if ($result->withRegistry) {
                // minutos configurados en la tabla
                $oRow->overDefaultMins = SDelayReportUtils::getExtraTime($result);
                // minutos por turnos de más de 8 horas
                $oRow->overScheduleMins = SDelayReportUtils::getExtraTimeBySchedule($result, $oRow->inDateTime, $oRow->inDateTimeSch,
                                                                                    $oRow->outDateTime, $oRow->outDateTimeSch);
            }

            if ((($oRow->overWorkedMins + $oRow->overMinsByAdjs) >= 20) || (($oRow->overScheduleMins + $oRow->overMinsByAdjs) >= 60)) {
                if ($comments != null) {
                    if ($comments->where('key_code','overWorkedMins')->first()['value']) {
                        $oRow->isDayChecked = true;
                    }
                }
            }

            $oRow = SDataProcess::checkTypeDay($result, $oRow);
        }

        return $oRow;
    }

    /**
     * Retorna un valor dependiendo de donde fue obtenido el horario,
     * si desde los horarios asignados o los programados
     *
     * @param \App\SUtils\SDateComparison $result
     * 
     * @return int \SCons::FROM_WORKSH o \SCons::FROM_ASSIGN
     */
    public static function getOrigin($result)
    {
        if ($result->auxWorkshift != null) {
            return \SCons::FROM_WORKSH;
        }
        if ($result->auxScheduleDay != null) {
            return \SCons::FROM_ASSIGN;
        }
        
        return 0;
    }

    /**
     * Retorna TRUE si la diferencia en minutos entre las fechas es mayor o igual a 24 horas,
     * esto si ambas son diferentes de NULL
     *
     * @param string $inDateTime
     * @param string $outDateTime
     * 
     * @return boolean
     */
    public static function gapInAndOut24Hr($inDateTime, $outDateTime)
    {
        if ($inDateTime != null && $outDateTime != null) {
            $oComparison = SDelayReportUtils::compareDates($inDateTime, $outDateTime);

            return $oComparison->diffMinutes >= 1440;
        }

        return false;
    }

    /**
     * Asigna al renglón en base al tipo de día si es un día festivo, descanso, etc.
     *
     * @param \App\SUtils\SDateComparison $result
     * @param Object $oRow
     * 
     * @return Object $oRow
     */
    public static function checkTypeDay($result, $oRow)
    {
        if ($result->auxWorkshift != null) {
            $oRow->isTypeDayChecked = true;

            $oRow = SDataProcess::setTypeDay($result->auxWorkshift->type_day_id, $oRow);
        }
        
        return $oRow;
    }

    /**
     * Agrega eventos, descansos y festivos al renglón
     *
     * @param array App\SUtils\SRegistryRow $lData53
     * @param query $qWorkshifts
     * 
     * @return array App\SUtils\SRegistryRow
     */
    public static function addEventsDaysOffAndHolidays($lData53, $qWorkshifts, $comments = null)
    {
        foreach ($lData53 as $oRow) {
            /**
             * Se comenta esta condición para que aunque el día no sea laborable, busque incidencias, eventos y festivos
             */
            // if (! $oRow->workable) {
            //     continue;
            // }
            if ($oRow->outDateTimeSch == null) {
                $sDt = Carbon::parse($oRow->outDateTime);
            }
            else {
                $sDt = Carbon::parse($oRow->outDateTimeSch);
            }

            $lAbsences = prePayrollController::searchAbsence($oRow->idEmployee, $sDt->toDateString());
                    
            if (sizeof($lAbsences) > 0) {
                // $incidentsType = \DB::table('type_incidents')->get();
                foreach ($lAbsences as $absence) {
                    $key = explode("_", $absence->external_key);

                    $abs = [];
                    $abs['id'] = $absence->id;
                    $abs['id_emp'] = $key[0];
                    $abs['id_abs'] = $key[1];
                    $abs['is_external'] = $absence->external_key != "0_0";
                    $abs['nts'] = $absence->nts;
                    $abs['type_name'] = $absence->type_name;
                    $abs['type_id'] = $absence->type_id;
                    $abs['is_allowed'] = $absence->is_allowed;
                    $oRow->others = $oRow->others."".$absence->type_name.". ";

                    // $incident = $incidentsType->where('id',$absence->type_id)->first();

                    if ($comments != null) {
                        if ($comments->where('key_code',$absence->type_id)->first()['value']) {
                            $oRow->isDayChecked = true;
                        } else if ($comments->where('key_code',$absence->type_id)->first()['value'] == 0) {
                            $oRow->isDayChecked = false;
                        }
                    }

                    $oRow->events[] = $abs;
                }
            }

            if ($oRow->outDateTimeSch == null) {
                $sOutDt = Carbon::parse($oRow->outDateTime);
            }
            else {
                $sOutDt = Carbon::parse($oRow->outDateTimeSch);
            }

            $holidays = SDataProcess::getHolidays($sOutDt->toDateString());

            if ($holidays == null) {
                continue;
            }

            $num = sizeof($holidays);

            if ($num > 0) {
                $oRow->isHoliday = $num;
                $oRow->others = $oRow->others.'Festivo. ';
                $oRow->isDayChecked = false;
                $oRow->comments = str_replace("Sin checadas. ", "", $oRow->comments);
            }

            if ($oRow->isTypeDayChecked || $oRow->hasAssign) {
                continue;
            }

            $lWorks = clone $qWorkshifts;
            $events = SDelayReportUtils::checkEvents($lWorks, $oRow->idEmployee, $sDt->toDateString());

            if ($events == null) {
                continue;
            }

            foreach ($events as $event) {
                $oRow = SDataProcess::setTypeDay($event->type_day_id, $oRow);
            }
        }

        $lData53_2 = $lData53;

        return $lData53_2;
    }

    /**
     * En base al tipo de día determina si el empleado tiene incapacidad, vacaciones, festivos
     * o descansos para el día en cuestión
     *
     * @param int $typeDay
     * @param App\SUtils\SRegistryRow $oRow
     * 
     * @return App\SUtils\SRegistryRow
     */
    public static function setTypeDay($typeDay, $oRow)
    {
        if ($typeDay == \SCons::T_DAY_NORMAL) {
            return $oRow;
        }

        $text = "";

        switch ($typeDay) {
            case \SCons::T_DAY_INHABILITY:
                $oRow->dayInhability++;
                $text = "Incapacidad";
                break;

            case \SCons::T_DAY_VACATION:
                $oRow->dayVacations++;
                $text = "Vacaciones";
                break;

            case \SCons::T_DAY_HOLIDAY:
                // $oRow->isHoliday++;
                // $text = "Festivo";
                break;

            case \SCons::T_DAY_DAY_OFF:
                $oRow->isDayOff++;
                break;

            default:
                # code...
                break;
        }

        $oRow->others = $oRow->others.$text.". ";

        return $oRow;
    }

    public static function getHolidays($sDt)
    {
        /**
         * SELECT 
         *       distinct h.id, h.name, ha.date
         *   FROM
         *       holiday_assign ha
         *           INNER JOIN
         *       holidays h ON ha.holiday_id = h.id
         *           LEFT JOIN
         *       areas a ON ha.area_id = a.id
         *           LEFT JOIN
         *       departments da ON a.id = da.area_id
         *           LEFT JOIN
         *       employees ea ON da.id = ea.department_id
         *           LEFT JOIN
         *       departments d ON ha.department_id = d.id
         *           LEFT JOIN
         *       employees e ON d.id = e.department_id
         *   WHERE
         *       ha.date = '2020-09-22'
         *       AND ha.is_delete = FALSE
         *       AND (ha.employee_id = 24 OR ea.id = 24 OR e.id = 24);
         */

        // \DB::enableQueryLog();

        // Consulta anterior para ingresar días festivos a la prenómina 01-06-2021
        /*
        $holidays = \DB::table('holiday_assign AS ha')
                        ->join('holidays AS h', 'ha.holiday_id', '=', 'h.id')
                        ->leftJoin('areas AS a', 'ha.area_id', '=', 'a.id')
                        ->leftJoin('departments AS da', 'da.area_id', '=', 'a.id')
                        ->leftJoin('employees AS ea', 'da.id', '=', 'ea.department_id')
                        ->leftJoin('departments AS d', 'd.id', '=', 'ha.department_id')
                        ->leftJoin('employees AS e', 'd.id', '=', 'e.department_id')
                        ->select('h.id AS h_id', 'ha.date', 'h.name')
                        ->where('ha.date', $sDt)
                        ->where('ha.is_delete', false)
                        ->where(function ($query) use ($idEmployee) {
                            $query->where('ha.employee_id', '=', $idEmployee)
                            ->orWhere('ea.id', '=', $idEmployee)
                            ->orWhere('e.id', '=', $idEmployee);
                        })
                        ->distinct('h_id')
                        ->get();
        */

        // Consulta días festivos apartir de la fecha de la tabla holidays

        $holidays = \DB::table('holidays AS h')
                            ->select('h.id AS h_id', 'h.fecha', 'h.name')
                            ->where('h.fecha', $sDt)
                            ->where('h.is_delete', false)
                            ->distinct('h_id')
                            ->get();
        // dd(\DB::getQueryLog());
        
        return $holidays;
    }

    /**
     * Determina retardos, tiempo extra, salida anticipada y en base a esto
     * agrega banderas de checar horario
     *
     * @param array[App\SUtils\SRegistryRow] $lData
     * @param array[id_employee => genera horas extra] $aEmployeeOverTime
     * 
     * @return array[App\SUtils\SRegistryRow] $lData
     */
    public static function addDelaysAndOverTime($lData, $aEmployeeOverTime, $sEndDate, $comments = null)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $workshifts = \DB::table('workshifts')->where('is_delete',0)->get();
        $consumAdjs = [];
        foreach ($lData as $oRow) {
            if (! $oRow->workable &&
                    ($aEmployeeOverTime[$oRow->idEmployee] == \SCons::ET_POL_NEVER || 
                    $aEmployeeOverTime[$oRow->idEmployee] == \SCons::ET_POL_SOMETIMES)) {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;

                $oRow->overMinsTotal = 0;

                continue;
            }

            if ($oRow->hasChecks) {
                $cIn = false;
                $cOut = false;

                if (! $oRow->isSpecialSchedule) {
                    if ($oRow->hasCheckIn) {
                        $mayBeOverTime = false;
                        $cIn = SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $mayBeOverTime);
                    }
                    if ($oRow->hasCheckOut) {
                        //$mayBeOverTime = $aEmployeeOverTime[$oRow->idEmployee];
                        $mayBeOverTime = false;
                        $cOut = SDataProcess::isCheckSchedule($oRow->outDateTime, $oRow->outDateTimeSch, $mayBeOverTime);
                    }
                    if (($cIn && $cOut) || ! $oRow->hasSchedule) {
                        $oRow = SDataProcess::determineSchedule($oRow, $sEndDate, $workshifts);
                    }
                }

                // minutos de retardo
                $mins = SDataProcess::getDelayMins($oRow->inDateTime, $oRow->inDateTimeSch);
                if ($mins > 0) {
                    $hasDelay = true;

                    // Ajuste de prenómina
                    $date = $oRow->inDate == null ? $oRow->inDateTime : $oRow->inDate;
                    $time = strlen($oRow->inDateTime) > 10 ? substr($oRow->inDateTime, -8) : null;
                    $adjs = SPrepayrollAdjustUtils::getAdjustsOfRow($date, $date, $oRow->idEmployee, \SCons::PP_TYPES['OR']);

                    if (count($adjs) > 0) {
                        foreach ($adjs as $adj) {
                            if (! in_array($adj->id, $consumAdjs)) {
                                if ($adj->apply_to == 1) {
                                    if ($adj->dt_date == $date) {
                                        if ($time == $adj->dt_time) {
                                            $hasDelay = false;
                                            $consumAdjs[] = $adj->id;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($hasDelay) {
                        $oRow->entryDelayMinutes = $mins;
                        $oRow->comments = $oRow->comments."Retardo. ";
                        if ($comments != null) {
                            if ($comments->where('key_code','entryDelayMinutes')->first()['value']) {
                                $oRow->isDayChecked = true;
                            }
                        }
                    }
                }
                else {
                    $oRow->entryDelayMinutes = 0;
                }

                // minutos de salida anticipada
                if ($oRow->hasCheckOut) {
                    $oRow->prematureOut = SDataProcess::getPrematureTime($oRow->outDateTime, $oRow->outDateTimeSch);
                }
                else {
                    $oRow->prematureOut = null; 
                }

                $bWork8hr = true;
                $oRow->hasWorkedJourney8hr = SDataProcess::journeyCompleted($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch, $bWork8hr, $oRow->workJourneyMins);
                $bWork8hr = false;
                $bJorneyCompleted = SDataProcess::journeyCompleted($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch, $bWork8hr, $oRow->workJourneyMins);
                if ($bJorneyCompleted) {
                    $extendJourney = SDelayReportUtils::compareDates($oRow->inDateTimeSch, $oRow->outDateTimeSch);
                    if ($aEmployeeOverTime[$oRow->idEmployee] == 2 || ($aEmployeeOverTime[$oRow->idEmployee] == 3 && $extendJourney->diffMinutes > 480)) {
                        // minutos extra trabajados y filtrados por bandera de "genera horas extra"
                        // Ajuste de prenómina
                        $date = $oRow->outDate == null ? $oRow->outDateTime : $oRow->outDate;
                        $time = strlen($oRow->outDateTime) > 10 ? substr($oRow->outDateTime, -8) : null;
                        
                        $oRow->overWorkedMins = SDataProcess::getOverTime($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch);
                        if ($oRow->overWorkedMins > 0) {
                            $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['DHE'], $oRow->idEmployee);
                            $discountMins = 0;
                            if (count($adjs) > 0) {
                                foreach ($adjs as $adj) {
                                    $discountMins += $adj->minutes;
                                }
                            }

                            if ($oRow->overWorkedMins >= $discountMins) {
                                $oRow->overMinsByAdjs = - $discountMins;
                            }
                            else {
                                $oRow->overMinsByAdjs = - $oRow->overWorkedMins;
                            }
                        }
                    }
                }
                else {
                    if (! $oRow->workable && $oRow->hasCheckIn && $oRow->hasCheckOut && $aEmployeeOverTime[$oRow->idEmployee] == \SCons::ET_POL_ALWAYS) {
                        $workedTime = SDelayReportUtils::compareDates($oRow->inDateTime, $oRow->outDateTime);
                        $workedMins = $workedTime->diffMinutes;

                        // si el tiempo trabajado es menor al máximo de tiempo configurado
                        if ($workedMins < $config->maxOvertimeJourneyMinutes && $workedMins > 0) {
                            $oRow->overWorkedMins += $workedMins;
                            $oRow->overDefaultMins = 0;
                            $oRow->overScheduleMins = 0;

                            $oRow->comments = $oRow->comments."Jornada TE. ";
                            if ($comments != null) {
                                if ($comments->where('key_code','isIncompleteTeJourney')->first()['value']) {
                                    $oRow->isDayChecked = true;
                                }
                            }
                            $oRow->isIncompleteTeJourney = true;

                            $date = $oRow->outDate == null ? $oRow->outDateTime : $oRow->outDate;
                            $time = strlen($oRow->outDateTime) > 10 ? substr($oRow->outDateTime, -8) : null;
                            $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['DHE'], $oRow->idEmployee);
                            $discountMins = 0;
                            if (count($adjs) > 0) {
                                foreach ($adjs as $adj) {
                                    $discountMins += $adj->minutes;
                                }
                            }

                            if ($oRow->overWorkedMins >= $discountMins) {
                                $oRow->overMinsByAdjs = - $discountMins;
                            }
                            else {
                                $oRow->overMinsByAdjs = - $oRow->overWorkedMins;
                            }

                            // si el día es domingo quita la prima
                            if (SDateTimeUtils::dayOfWeek($oRow->outDate) == Carbon::SUNDAY) {
                                $oRow->removeSunday = true;
                            }
                        }
                        else {
                            $oRow->overWorkedMins = 0;
                            $oRow->overDefaultMins = 0;
                            $oRow->overScheduleMins = 0;

                            if ($oRow->hasSchedule && $oRow->inDateTimeSch != null && $oRow->outDateTimeSch != null) {
                                $oRow->comments = $oRow->comments."Jornada incompleta. ";
                                $oRow->isOverJourney = false;
                            }
                        }
                    }
                    else {
                        $oRow->overWorkedMins = 0;
                        $oRow->overDefaultMins = 0;
                        $oRow->overScheduleMins = 0;
                        
                        if ($oRow->hasSchedule && $oRow->inDateTimeSch != null && $oRow->outDateTimeSch != null) {
                            $oRow->comments = $oRow->comments."Jornada incompleta. ";
                            $oRow->isOverJourney = false;
                        }
                    }
                }
            }
            else {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;

                $oRow->overMinsTotal = 0;
            }

            // Ajuste de prenómina
            $date = $oRow->outDate == null ? $oRow->outDateTime : $oRow->outDate;
            $time = strlen($oRow->outDateTime) > 10 ? substr($oRow->outDateTime, -8) : null;
            $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['AHE'], $oRow->idEmployee);

            if (count($adjs) > 0) {
                $minsExtraByAdj = 0;
                foreach ($adjs as $adj) {
                    $minsExtraByAdj += $adj->minutes;
                }

                $oRow->overMinsByAdjs = $oRow->overMinsByAdjs + $minsExtraByAdj;
            }

            if ($oRow->hasChecks) {
                $cIn = false;
                $cOut = false;
                $adjIn = SPrepayrollAdjustUtils::hasTheAdjustType($oRow->adjusts, \SCons::PP_TYPES['JE']);
                $adjOut = SPrepayrollAdjustUtils::hasTheAdjustType($oRow->adjusts, \SCons::PP_TYPES['JS']);
                if ($oRow->hasCheckIn) {
                    $mayBeOverTime = false;
                    $cIn = SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $mayBeOverTime);
                    if ($cIn && !$adjIn) {
                        $oRow->comments = $oRow->comments."Entrada atípica. ";
                        if ($comments != null) {
                            if ($comments->where('key_code','isAtypicalIn')->first()['value']) {
                                $oRow->isDayChecked = true;
                            }
                        }
                        $oRow->isAtypicalIn = true;
                    }
                }
                if ($oRow->hasCheckOut) {
                    //$mayBeOverTime = $aEmployeeOverTime[$oRow->idEmployee];
                    $mayBeOverTime = false;
                    $cOut = SDataProcess::isCheckSchedule($oRow->outDateTime, $oRow->outDateTimeSch, $mayBeOverTime);
                    if ($cOut && !$adjOut) {
                        $oRow->comments = $oRow->comments."Salida atípica. ";
                        if ($comments != null) {
                            if ($comments->where('key_code','isAtypicalOut')->first()['value']) {
                                $oRow->isDayChecked = true;
                            }
                        }
                        $oRow->isAtypicalOut = true;
                    }
                }
                if (($cIn || $cOut) && (! $adjIn && ! $adjOut)) {
                    $oRow->comments = $oRow->comments."Revisar horario. ";
                    if ($comments != null) {
                        if ($comments->where('key_code','isCheckSchedule')->first()['value']) {
                            $oRow->isDayChecked = true;
                        }
                    }
                    $oRow->isCheckSchedule = true;
                }

                if ($oRow->isAtypicalOut && $oRow->isAtypicalIn) {
                    $oRow->overDefaultMins = 0;
                }
            }

            if ((($oRow->overWorkedMins + $oRow->overMinsByAdjs) >= 20) || (($oRow->overScheduleMins + $oRow->overMinsByAdjs) >= 60)) {
                if ($comments != null) {
                    if ($comments->where('key_code','overWorkedMins')->first()['value']) {
                        $oRow->isDayChecked = true;
                    }
                }
            }

            // suma de minutos extra totales.
            $oRow->overMinsTotal = $oRow->overWorkedMins + $oRow->overDefaultMins + $oRow->overScheduleMins + $oRow->overMinsByAdjs;
        }

        return $lData;
    }

    /**
     * Retorna un entero con los minutos de retardo del empleado
     *
     * @param string $inDateTime
     * @param string $inDateTimeSch
     * 
     * @return int minutos de retardo
     */
    public static function getDelayMins($inDateTime, $inDateTimeSch)
    {
        if ($inDateTime == null || $inDateTimeSch == null) {
            return null;
        }

        $oComparison = SDelayReportUtils::compareDates($inDateTimeSch, $inDateTime);

        if ($oComparison->diffMinutes > 0) {
            return $oComparison->diffMinutes;
        }
        else {
            return 0;
        }
    }

    /**
     * Retorna minutos de salida anticipada
     *
     * @param string $outDateTime
     * @param string $outDateTimeSch
     * 
     * @return int minutos de salida anticipada
     */
    public static function getPrematureTime($outDateTime, $outDateTimeSch)
    {
        if ($outDateTime == null || $outDateTimeSch == null) {
            return null;
        }

        $oComparison = SDelayReportUtils::compareDates($outDateTime, $outDateTimeSch);

        if ($oComparison->diffMinutes > 0) {
            return $oComparison->diffMinutes;
        }
        else {
            return 0;
        }
    }

    /**
     * Determina el tiempo extra recorriendo la hora de salida base,
     * esto sumando los minutos de retardo a la entrada
     *
     * @param string $inDateTime
     * @param string $inDateTimeSch
     * @param string $outDateTime
     * @param string $outDateTimeSch
     * 
     * @return int tiempo extra (minutos)
     */
    public static function getOverTime($inDateTime, $inDateTimeSch, $outDateTime, $outDateTimeSch)
    {
        if ($inDateTime == null || $inDateTimeSch == null || $outDateTime == null || $outDateTimeSch == null) {
            return null;
        }

        $delay = SDataProcess::getDelayMins($inDateTime, $inDateTimeSch);
        $timeSchOut = $outDateTimeSch;

        if ($delay > 0) {
            $date = Carbon::parse($outDateTimeSch);
            $date->addMinutes($delay);
            $timeSchOut = $date->toDateTimeString();
        }

        $oComparison = SDelayReportUtils::compareDates($outDateTime, $timeSchOut);

        if ($oComparison->diffMinutes < 0) {
            return abs($oComparison->diffMinutes);
        }

        return 0;
    }

    /**
     * Determina si el trabajador completó su jornada laboral, o si hizo más de 8 horas
     *
     * @param string $inDateTime
     * @param string $inDateTimeSch
     * @param string $outDateTime
     * @param string $outDateTimeSch
     * @param boolean $bWork8hr si este parámetro es TRUE la función 
     *                          indicará únicamente si trabajó 8 horas o más o no
     * 
     * @return boolean
     */
    public static function journeyCompleted($inDateTime, $inDateTimeSch, $outDateTime, $outDateTimeSch, $bWork8hr, $workJourneyMins)
    {
        if ($inDateTime == null || $inDateTimeSch == null || $outDateTime == null || $outDateTimeSch == null) {
            return false;
        }

        $config = \App\SUtils\SConfiguration::getConfigurations();
        $comparisonCheck = SDelayReportUtils::compareDates($inDateTime, $outDateTime);

        if ($workJourneyMins > 0) {
            return $comparisonCheck->diffMinutes >= ($workJourneyMins - $config->toleranceMinutes);
        }
        
        $comparisonSched = SDelayReportUtils::compareDates($inDateTimeSch, $outDateTimeSch);

        if ($bWork8hr) {
            return $comparisonCheck->diffMinutes >= (480 - $config->toleranceMinutes);
        }

        return $comparisonCheck->diffMinutes >= ($comparisonSched->diffMinutes - $config->toleranceMinutes)
                || $comparisonCheck->diffMinutes >= (480 - $config->toleranceMinutes);
    }

    /**
     * Compara el horario programado con el horario de la checada para determinar
     * si se salió del rango y en base a esto checar horario.
     * El parámetro $isOut es auxiliar para saber si la checada es de salida y no agregar 
     * la leyenda, ya que puede ser tiempo extra.
     *
     * @param string $sDateTime
     * @param string $sDateTimeSch
     * @param boolean $isOut
     * 
     * @return boolean
     */
    public static function isCheckSchedule($sDateTime, $sDateTimeSch, $isOut)
    {
        if ($sDateTime == null || $sDateTimeSch == null) {
            return false;
        }

        $config = \App\SUtils\SConfiguration::getConfigurations();

        $comparison = SDelayReportUtils::compareDates($sDateTime, $sDateTimeSch);

        if ($isOut) {
            return $comparison->diffMinutes > 0 && $comparison->diffMinutes > $config->maxGapCheckSchedule;
        }
        
        return abs($comparison->diffMinutes) > $config->maxGapCheckSchedule;
    }

    /**
     * Determina por las checadas de entrada y salida si el horario pertenece a alguno
     * de los que otorgan tiempo extra y los asigna al empleado junto con sus horas extra
     *
     * @param 'App\SUtils\SRegistryRow' $oRow
     * 
     * @return 'App\SUtils\SRegistryRow'
     */
    public static function determineSchedule($oRow, $sEndDate, $workshifts)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $inDate = Carbon::parse($oRow->inDateTime)->toDateString();
        $outDate = Carbon::parse($oRow->outDateTime)->toDateString();
        $oRow->isOnSchedule = true;
        foreach ($workshifts as $workshift) {
            $comparisonIn = SDelayReportUtils::compareDates($oRow->inDateTime, $inDate.$workshift->entry);
            $comparisonOut = SDelayReportUtils::compareDates($oRow->outDateTime, $outDate.$workshift->departure);
            
            if (abs($comparisonIn->diffMinutes) <= $config->maxGapSchedule && abs($comparisonOut->diffMinutes) <= $config->maxGapSchedule) {
                $oRow->inDateTimeSch = $inDate." ".$workshift->entry;
                $oRow->outDateTimeSch = $outDate." ".$workshift->departure;
                $oRow->overDefaultMins = $workshift->overtimepershift > 0 ? $workshift->overtimepershift * 60 : 0;
                $oRow->workJourneyMins = $workshift->work_time * 60;

                $sScheduleProgrammed = $oRow->scheduleText;
                $sScheduleDetected = $workshift->name;
                $oRow->scheduleText = strtoupper($workshift->name)." (detectado)";

                /**
                 * Log del cambio en los horarios, programados vs detectados
                 */
                if (session()->has('logger')) {
                    session('logger')->log($oRow->idEmployee, 'cambio_horario', null, null, $sScheduleProgrammed, $sScheduleDetected);
                }

                return $oRow;
            }
        }

        $oRow->isOnSchedule = false;

        return $oRow;
    }

    /**
     * Determina faltas, es decir el empleado tiene asignado un horario y no tiene 
     * vacaciones, festivos, incidencias, etc
     *
     * @param array[App\SUtils\SRegistryRow] $lData
     * @param array[id_employee, beneficios] $aEmployeeBen
     * @return void
     */
    public static function addAbsences($lData, $aEmployeeBen, $comments = null)
    {
        $consumAdjs = [];
        foreach ($lData as $oRow) {
            $hasAbs = false;
            $absenceByOmission = false;
            switch ($aEmployeeBen[$oRow->idEmployee]) {
                case \SCons::BEN_POL_FREE:
                    # code...
                    // break;
                case \SCons::BEN_POL_EVENT:
                        # code...
                        break;
                case \SCons::BEN_POL_STRICT:
                    if ($oRow->hasSchedule) {
                        if (! $oRow->hasChecks) {
                            if (sizeof($oRow->events) == 0 && ! $oRow->isDayOff 
                                    && $oRow->isHoliday == 0 && $oRow->dayInhability == 0
                                    && $oRow->dayVacations == 0 && $oRow->workable) {
                                        $hasAbs = true;
                            }
                        }

                        if (! $hasAbs) {
                            $config = \App\SUtils\SConfiguration::getConfigurations();
                            if ($config->absenceWithOmissionOfChecks) {
                                if (in_array($oRow->employeeAreaId, $config->absenceWithOmissionOfChecksAreas)) {
                                    if (! $oRow->hasCheckOut && $oRow->hasCheckIn) {
                                        $date = $oRow->outDate;
                                        $time = null;
                                        $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['JS'], $oRow->idEmployee);
                                
                                        if (count($adjs) == 0) {
                                            $hasAbs = true;
                                            $absenceByOmission = true;
                                        }
                                    }
                                    if (! $hasAbs && $oRow->hasCheckOut && ! $oRow->hasCheckIn) {
                                        $date = $oRow->inDate;
                                        $time = null;
                                        $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 1, \SCons::PP_TYPES['JE'], $oRow->idEmployee);
                                
                                        if (count($adjs) == 0) {
                                            $hasAbs = true;
                                            $absenceByOmission = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    break;
                
                default:
                    # code...
                    break;
            }

            if ($hasAbs) {
                $withAbs = true;
                
                // Ajuste de prenómina
                $date = $oRow->inDate == null ? $oRow->inDateTime : $oRow->inDate;
                $time = strlen($oRow->inDateTime) > 10 ? substr($oRow->inDateTime, -8) : null;
                $adjs = SPrepayrollAdjustUtils::getAdjustsOfRow($date, $date, $oRow->idEmployee, \SCons::PP_TYPES['OF']);

                if (count($adjs) > 0) {
                    foreach ($adjs as $adj) {
                        if (! in_array($adj->id, $consumAdjs)) {
                            if ($adj->apply_to == 1) {
                                if ($adj->dt_date == $date) {
                                    if ($time == $adj->dt_time) {
                                        $withAbs = false;
                                        $consumAdjs[] = $adj->id;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($withAbs) {
                    $oRow->hasAbsence = true;
                    $oRow->comments = $oRow->comments . ($absenceByOmission ? "Falta por omitir checar. " : "Falta. ");
                    if ($comments != null) {
                        if ($comments->where('key_code','hasAbsence')->first()['value']) {
                            $oRow->isDayChecked = true;
                        }
                    }
                }
                else {
                    $oRow->hasAbsence = false;
                }
            }
        }

        return $lData;
    }

    /**
     * Agrega la prima dominical
     *
     * @param array[App\SUtils\SRegistryRow] $lData
     * 
     * @return array[App\SUtils\SRegistryRow]
     */
    public static function addSundayPay($lData)
    {
        $lPays = [];
        foreach ($lData as $oRow) {
            if (! $oRow->hasChecks || !$oRow->hasCheckOut) {
                continue;
            }

            if ($oRow->outDate != null) {
                // En caso de que el día solo se haya trabajado horas extra, no se da la prima dominical
                if (SDateTimeUtils::dayOfWeek($oRow->outDate) == Carbon::SUNDAY && ! $oRow->removeSunday) {
                    if (! isset($lPays[$oRow->outDate."_".$oRow->idEmployee])) {
                        $oRow->isSunday++;
                        $lPays[$oRow->outDate."_".$oRow->idEmployee] = 1;
                    }
                }
            }
        }

        return $lData;
    }

    /**
     * Procesa las checadas de un día en específico y determina si 
     * el empleado checó más de una vez en el momento
     *
     * @param \Illuminate\Database\Eloquent\Collection $lCheks
     * 
     * @return array con las checadas válidas
     */
    public static function filterDoubleCheks($lCheks)
    {
        if (sizeof($lCheks) == 0) {
            return $lCheks;
        }

        $lNewChecks = array();

        $oCheckIn = null;
        $oCheckOut = null;
        foreach ($lCheks as $check) {
            if ($check->type_id == \SCons::REG_IN) {
                if ($oCheckOut != null) {
                    $lNewChecks[] = $oCheckOut;
                }
                $oCheckOut = null;
                if ($oCheckIn == null) {
                    $oCheckIn = $check;
                }
                else {
                    // diferencia entre checkIns es mucha se agregan las 2
                    $chekDate = $check->date.' '.$check->time;
                    $chekODate = $oCheckIn->date.' '.$oCheckIn->time;
                    $comparison = SDelayReportUtils::compareDates($chekDate, $chekODate);
                    if (abs($comparison->diffMinutes) >= 360) {
                        $lNewChecks[] = $oCheckIn;
                    }
                    $oCheckIn = $check;
                }
            }
            else {
                if ($oCheckIn != null) {
                    $lNewChecks[] = $oCheckIn;
                }
                $oCheckIn = null;

                if ($oCheckOut != null) {
                    $chekDate = $check->date.' '.$check->time;
                    $chekODate = $oCheckOut->date.' '.$oCheckOut->time;
                    $comparison = SDelayReportUtils::compareDates($chekDate, $chekODate);
                    if (abs($comparison->diffMinutes) >= 360) {
                        $lNewChecks[] = $oCheckOut;
                        $oCheckOut = $check;
                    }
                    else {
                        $oCheckOut = $check;
                    }
                }
                else {
                    $oCheckOut = $check;
                }
            }
        }

        if ($oCheckIn != null) {
            $lNewChecks[] = $oCheckIn;
        }

        if ($oCheckOut != null) {
            $lNewChecks[] = $oCheckOut;
        }

        /**
         * Log de las checadas omitidas de los empleados que registran varias veces entrada o salida
         */
        if (count($lCheks) != count($lNewChecks)) {
            foreach ($lCheks as $indexCheck) {
                if (! in_array($indexCheck, $lNewChecks) && session()->has('logger')) {
                    session('logger')->log($indexCheck->employee_id, 'checada_omitida', $indexCheck->id, null, null, null);
                }
            }
        }

        return $lNewChecks;
    }

    /**
     * En base al horario del empleado determina si la checada
     * es de entrada o salida y retorna el arreglo con el tipo modificado
     *
     * @param array[SRegistry] $lCheks
     * @param string $sDate
     * 
     * @return array[SRegistry]
     */
    public static function manageCheks($lCheks, $sDate)
    {
        if (sizeof($lCheks) == 0) {
            return $lCheks;
        }

        $lNewChecks = array();

        $registry = (object) [
            'date' => $sDate,
            'time' => '12:00:00',
            'type_id' => 1
        ];

        $lWorkshifts = SDelayReportUtils::getWorkshifts($sDate, $sDate, 0, []);
        foreach($lCheks as $auxCheck) break;
        $result = SDelayReportUtils::getSchedule($sDate, $sDate, $auxCheck->employee_id, $registry, clone $lWorkshifts, \SCons::REP_HR_EX);

        if ($result == null || ($result->auxScheduleDay != null && !$result->auxScheduleDay->is_active)) {
            return SDataProcess::filterDoubleCheks($lCheks);
        }

        $config = \App\SUtils\SConfiguration::getConfigurations();

        $inTime = "";
        $outTime = "";
        if ($result->auxWorkshift != null) {
            $inTime = $result->auxWorkshift->entry;
            $outTime = $result->auxWorkshift->departure;
        }
        else {
            $inTime = $result->auxScheduleDay->entry;
            $outTime = $result->auxScheduleDay->departure;
        }

        $lRegistries = SDataProcess::filterDoubleCheks($lCheks);

        $inDateTime = $sDate.' '.$inTime;
        $outDateTime = $sDate.' '.$outTime;
        $originalChecks = clone collect($lRegistries);
        foreach ($lRegistries as $oCheck) {
            $check = clone $oCheck;
            $checkDateTime = $check->date.' '.$check->time;

            $comparisonIn = SDelayReportUtils::compareDates($inDateTime, $checkDateTime);
            $comparisonOut = SDelayReportUtils::compareDates($outDateTime, $checkDateTime);

            // si ni entrada ni salida coinciden con el horario regresa las checadas originales
            if (abs($comparisonIn->diffMinutes) > $config->maxGapSchedule && abs($comparisonOut->diffMinutes) > $config->maxGapSchedule) {
                $lNewChecks = $originalChecks;
                break;
            }

            if (abs($comparisonIn->diffMinutes) <= $config->maxGapSchedule) {
                if ($check->type_id != \SCons::REG_IN && session()->has('logger')) {
                    /**
                     * Log de los empleados que checaron salida por entrada
                     */
                    session('logger')->log($check->employee_id, 'checada_cambio', $check->id, $check->type_id, null, null);
                }

                $check->type_id = \SCons::REG_IN;
            }
            else {
                if (abs($comparisonOut->diffMinutes) <= $config->maxGapSchedule) {
                    if ($check->type_id != \SCons::REG_OUT && session()->has('logger')) {
                        /**
                         * Log de los empleados que checaron entrada por salida
                         */
                        session('logger')->log($check->employee_id, 'checada_cambio', $check->id, $check->type_id, null, null);
                    }

                    $check->type_id = \SCons::REG_OUT;
                }
            }

            $lNewChecks[] = $check;
        }

        return $lNewChecks;
    }

    private static function manageOneCheck($sDate, $idEmployee, $registries, $lWorkshifts, $sEndDate)
    {
        $oRegistry = $registries[0];
        $config = \App\SUtils\SConfiguration::getConfigurations();

        // Si la checada es una entrada del último día del rango y es después de la hora configurada
        if ($sEndDate == $sDate && $oRegistry->type_id == \SCons::REG_IN && $sEndDate == $oRegistry->date && $oRegistry->time >= $config->time_last_check) {
            // Consulta si no tiene horario el día siguiente o no es nocturno, deja la checada
            $oNextDay = Carbon::parse($sEndDate);
            $oNextDay->addDay();
            $oAuxRegistry = new \stdClass();
            $oAuxRegistry->date = $oNextDay->toDateString();
            $oAuxRegistry->time = "12:00:00";
            $oAuxRegistry->type_id = \SCons::REG_OUT;

            $resultAux = SDelayReportUtils::getSchedule($oNextDay->toDateString(), $oNextDay->toDateString(), $idEmployee, $oAuxRegistry, clone $lWorkshifts, \SCons::REP_HR_EX);
            if (is_null($resultAux) || (! is_null($resultAux) && SDelayReportUtils::isNight($resultAux))) {
                return 3;
            }
        }
        
        $result = SDelayReportUtils::getSchedule($sDate, $sDate, $idEmployee, $oRegistry, clone $lWorkshifts, \SCons::REP_HR_EX);
        if ($result == null || ($result->auxScheduleDay != null && !$result->auxScheduleDay->is_active)) {
            return 0;
        }

        $sdate = $oRegistry->date;
        $comparison = null;
        
        // if ($registries[0]->type_id == \SCons::REG_OUT) {
        //     $comparison = SDelayReportUtils::compareDates($sdate.' '.$oRegistry->time, $sdate.' 06:30:00');

        //     return (abs($comparison->diffMinutes) <= $config->maxGapMinutes) ? 1 : 0;
        // }
        if ($oRegistry->type_id == \SCons::REG_IN) {
            $comparison = SDelayReportUtils::compareDates($sdate.' '.$oRegistry->time, $sdate.' 22:30:00');
            
            if (abs($comparison->diffMinutes) <= $config->maxGapMinutes) {
                $oDateAux = Carbon::parse($sdate);
                $oDateAux->subDay();
                $oFoundRegistry = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_IN, '22:30:00');

                if ($oFoundRegistry == null) {
                    return 2;
                }
                // else {
                //     $comparison = SDelayReportUtils::compareDates($oFoundRegistry->date.' '.$oFoundRegistry->time, $oDateAux->toDateString().' 22:30:00');

                //     if (abs($comparison->diffMinutes) <= $config->maxGapMinutes) {
                //         if (! is_array($registries)) {
                //             $registries = $registries->toArray();
                //         }
                //         array_unshift($registries, $oFoundRegistry);
                //         return [0, $registries];
                //     }
                //     else {
                //         return 2;
                //     }
                // }
            }
        }

        return 0;
    }

    /**
     * Verifica si se han reportado todos los días consultados en el rango de fechas por empleado.
     * Si un día no se ha reportado, se agrega este renglón a la colección.
     * 
     * @param string $sStartDate
     * @param string $sEndDate
     * @param array $lData
     * @param array $aEmployees
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function completeDays($sStartDate, $sEndDate, $lData, $aEmployees, $qWorkshifts, $comments = null)
    {
        $aDates = [];
        $oStartDate = Carbon::parse($sStartDate);
        $oEndDate = Carbon::parse($sEndDate);
        $oDate = clone $oStartDate;

        /**
         * crea un arreglo con los días a consultar
         */
        while ($oDate->lessThanOrEqualTo($oEndDate)) {
            $aDates[] = $oDate->toDateString();
            $oDate->addDay();
        }

        $lRowsToAdd = [];
        foreach ($aEmployees as $idEmployee) {
            $employeeRows = collect($lData);
            $employeeRows = $employeeRows->where('idEmployee', $idEmployee);
            
            // Si no hay renglones del empleado nos pasamos al siguiente
            if (count($employeeRows) == 0) {
                continue;
            }
            foreach ($aDates as $sDate) {
                $lEmployeeRows = clone $employeeRows;
                // Consulta los renglones del empleado con salida del día actual
                $dayRows = $lEmployeeRows->where('outDate', $sDate);

                // Si no hay renglones con salida del día actual
                if ($dayRows->count() == 0) {
                    $lEmployeeRows = clone $employeeRows;
                    // Consulta si existen renglones con entrada que pudieran cubrir el día actual
                    $dayRows = $lEmployeeRows->whereBetween('inDateTime', [$sDate.' 00:00:00', $sDate.' 17:30:00']);

                    // Si hay renglones que pudieran cubrir el día actual no se agrega el renglón
                    if ($dayRows->count() > 0) {
                        continue;
                    }
                    // if ($dayRows->count() > 0 && $sDate != $sEndDate) {
                    //     continue;
                    // }

                    // Se obtiene el primer elemento del arreglo
                    foreach ($lEmployeeRows as $refRow) { break; }

                    $oNewRow = new SRegistryRow();

                    $oNewRow->idEmployee = $idEmployee;
                    $oNewRow->numEmployee = $refRow->numEmployee;
                    $oNewRow->employee = $refRow->employee;
                    $oNewRow->external_id = $refRow->external_id;
                    $oNewRow->inDate = $sDate;
                    $oNewRow->outDate = $sDate;
                    $oNewRow->inDateTime = $sDate;
                    $oNewRow->outDateTime = $sDate;
                    $oNewRow->hasChecks = false;
                    $oNewRow->hasCheckIn = false;
                    $oNewRow->hasCheckOut = false;
                    $oNewRow->comments = $oNewRow->comments."Sin checadas. ";
                    if ($comments != null) {
                        if ($comments->where('key_code','hasChecks')->first()['value'] ||
                            $comments->where('key_code','hasCheckIn')->first()['value'] ||
                            $comments->where('key_code','hasCheckOut')->first()['value']) {
                            if ($oNewRow->isHoliday < 1) {
                                $oNewRow->isDayChecked = true;
                            }
                            else {
                                $oNewRow->isDayChecked = false;
                            }
                        }
                    }

                    $registry = (object) [
                        'type_id' => \SCons::REG_OUT,
                        'time' => '18:00:00',
                        'date' => $sDate,
                        'employee_id' => $idEmployee,
                        'is_modified' => false
                    ];

                    $result = SDelayReportUtils::getSchedule($sStartDate, $sEndDate, $idEmployee, $registry, clone $qWorkshifts, \SCons::REP_HR_EX);

                    if (! is_null($result)) {
                        $result->withRegistry = false;
                    }

                    $oNewRow = SDataProcess::setDates($result, $oNewRow, $sDate, $comments);
                    
                    // Se agrega el renglón creado a la colección de renglones por arreglar
                    $lRowsToAdd[] = $oNewRow;
                }
            }
        }

        // Se agregan los renglones al arreglo
        $lData = collect(array_merge($lData, $lRowsToAdd));

        // Se ordenan los renglones por empleado y fecha de entrada
        $aSortInstructions = [
                                ['column' => 'idEmployee', 'order' => 'asc'],
                                ['column' => 'inDateTime', 'order' => 'asc'],
                                ['column' => 'outDateTime', 'order' => 'asc'],
                            ];

        $lData = SReportsUtils::multiPropertySort($lData, $aSortInstructions);

        return $lData;
    }

    public static function checkEvents() {
        $comments = commentsControl::select('key_code','value')->get();
        $events = \DB::table('type_incidents')->get();
        $newEvents = [];

        foreach($events as $ev) {
            if (is_null($comments->where('key_code', $ev->id)->first())) {
                array_push($newEvents, ['key_code' => $ev->id, 
                                        'Comment' => $ev->name, 
                                        'value' => false, 
                                        'created_by' => 1, 
                                        'updated_by' => 1, 
                                        'is_delete' => 0, 
                                        'created_at' => now(), 
                                        'updated_at' => now()
                                    ]);
            }
        }

        if (! is_null($newEvents)) {
            \DB::table('comments_control')->insert($newEvents);
        }
    }

    //Revisa el dia anterior en busca de una entrada solo si el dia del renglon es el dia inicial
    private static function checkDayBefore ($sStartDate, $sDate, $idEmployee, $payWay, $chekHourEntry)
    {
        $checkDayBefore = null;
        // $num = SDateUtils::getNumberOfDate($sDate, $payWay);
        $date = Carbon::parse($sDate);
        $dateIni = Carbon::parse($sStartDate);
        if ($date->eq($dateIni)) {
            $subDay = $dateIni->subDay();

            $checkDayBefore = \DB::table('registers AS r')
                                ->join('employees AS e', 'e.id', '=', 'r.employee_id')
                                ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                                ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                                ->where([
                                            ['r.employee_id', $idEmployee],
                                            ['r.date', $subDay->toDateString()],
                                            ['r.type_id', 1],
                                            ['r.time', '>=', $chekHourEntry],
                                            ['r.is_delete', 0]
                                        ])
                                ->select('r.*', 'd.id AS dept_id', 'e.num_employee', 'e.name', 'e.policy_extratime_id', 'e.external_id', 'd.area_id AS employee_area_id')
                                ->first();
        }

        return $checkDayBefore;
    }

    public static function putAdjustInRows($sStartDate, $sEndDate, $lRows){
        $lAdjusts = \DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.comments',
                                    'pa.apply_to',
                                    'pa.adjust_type_id',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id',
                                    'pa.apply_time'
                                    )
                        ->whereBetween('dt_date', [$sStartDate, $sEndDate])
                        ->where('is_delete', false)
                        ->get();

        foreach($lRows as $row) {
            $inDate = Carbon::parse($row->inDateTime);
            $outDate = Carbon::parse($row->outDateTime);

            $adjs = $lAdjusts->where('employee_id', $row->idEmployee)
                            ->whereBetween('dt_date', [$inDate->format('Y-m-d'), $outDate->format('Y-m-d')]);
            
            foreach($adjs as $adj){
                if($adj->apply_to == 1){
                    $tiime = $adj->dt_time != null ? (' '.$adj->dt_time) : '';
                    if($adj->apply_time){
                        $adj_date = Carbon::parse($adj->dt_date.$tiime);
                        $row_date = Carbon::parse($row->inDateTime);
                    }else{
                        $adj_date = Carbon::parse($adj->dt_date);
                        $row_date = Carbon::parse(is_null($row->inDate) ? $row->inDateTime : $row->inDate);
                    }

                    if($adj_date->eq($row_date)){
                        array_push($row->adjusts, $adj);
                    }
                }else if($adj->apply_to == 2){
                    $tiime = $adj->dt_time != null ? (' '.$adj->dt_time) : '';
                    if($adj->apply_time){
                        $adj_date = Carbon::parse($adj->dt_date.$tiime);
                        $row_date = Carbon::parse($row->outDateTime);
                    }else{
                        $adj_date = Carbon::parse($adj->dt_date);
                        $row_date = Carbon::parse($row->outDate);
                    }
                    if($adj_date->eq($row_date)){
                        array_push($row->adjusts, $adj);
                    }
                }
            }
        }

        return $lRows;
    }
}