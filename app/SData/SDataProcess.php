<?php namespace App\SData;

use Carbon\Carbon;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SDateTimeUtils;
use App\SUtils\SRegistryRow;
use App\Http\Controllers\prePayrollController;

class SDataProcess {

    /**
     * var_dump(Carbon::SUNDAY);     // int(0)
     * var_dump(Carbon::MONDAY);     // int(1)
     * var_dump(Carbon::TUESDAY);    // int(2)
     * var_dump(Carbon::WEDNESDAY);  // int(3)
     * var_dump(Carbon::THURSDAY);   // int(4)
     * var_dump(Carbon::FRIDAY);     // int(5)
     * var_dump(Carbon::SATURDAY);   // int(6)
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
        $data53 = SDataProcess::getSchedulesAndChecks($sStartDate, $sEndDate, $payWay, $lEmployees);
        
        $aEmployees = $lEmployees->pluck('id');
        $lWorkshifts = SDelayReportUtils::getWorkshifts($sStartDate, $sEndDate, $payWay, $aEmployees);
        $lData53_2 = SDataProcess::addEventsDaysOffAndHolidays($data53, $lWorkshifts);
        
        $aEmployeeBen = $lEmployees->pluck('ben_pol_id', 'id');
        $lDataWithAbs = SDataProcess::addAbsences($lData53_2, $aEmployeeBen);

        $aEmployeeOverTime = $lEmployees->pluck('is_overtime', 'id');
        $lData = SDataProcess::addDelaysAndOverTime($lDataWithAbs, $aEmployeeOverTime);

        $lDataWSun = SDataProcess::addSundayPay($lData);

        return $lDataWSun;
    }

    /**
     * Obtiene horarios y checadas, retorna una lista de objetos con los datos correspondientes
     *
     * @param string $sStartDate
     * @param string $sEndDate
     * @param int $payWay
     * @param [stdClass] $lEmployees
     * 
     * @return array SRegistryRow
     */
    public static function getSchedulesAndChecks($sStartDate, $sEndDate, $payWay, $lEmployees)
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
        $newRow = null;

        foreach ($lEmployees as $oEmployee) {
            $idEmployee = $oEmployee->id;
            $lAssigns = SDelayReportUtils::hasAnAssing($idEmployee, $oEmployee->dept_id, $sStartDate, $sEndDate);
            
            foreach ($aDates as $sDate) {

                $registries = clone $cRegistries;
                $registries = $registries->where('date', $sDate)
                                        ->where('employee_id', $idEmployee);

                //filtrar checadas repetidas
                $registries = SDataProcess::manageCheks($registries, $sDate);

                $bug = false;
                if (sizeof($registries) == 1) {
                    $res = SDataProcess::manageOneCheck($sDate, $idEmployee, $registries, $lWorkshifts);
                    
                    if ($res == 2) {
                        $regTemp = $registries;
                        $bug = true;
                        $registries = [];
                    }
                }
                toAbsences:
                if (sizeof($registries) > 0) {
                    foreach ($registries as $registry) {
                        $qWorkshifts = clone $lWorkshifts;
                        $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, clone $qWorkshifts, $sStartDate, $sEndDate);
        
                        $isNew = $theRow[0];
                        $newRow = $theRow[1];
                        $again = $theRow[2];
                        $fRegistry = $theRow[3];
        
                        if ($isNew) {
                            $lRows[] = $newRow;
                        }

                        if ($again) {
                            if ($fRegistry != null) {
                                $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $fRegistry, $lAssigns, clone $lWorkshifts, $sStartDate, $sEndDate);
                            }
                            else {
                                $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, clone $lWorkshifts, $sStartDate, $sEndDate);
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
                            'to_close' => true
                        ];

                        $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, clone $lWorkshifts, $sStartDate, $sEndDate);

                        $isNew = $theRow[0];
                        $newRow = $theRow[1];

                        if ($isNew) {
                            $lRows[] = $newRow;
                        }
                    }

                    $registry = (object) [
                                    'date' => $sDate,
                                    'time' => '12:00:00'
                                ];
                                
                    $result = SDelayReportUtils::getSchedule($sDate, $sDate, $idEmployee, $registry, clone $lWorkshifts, \SCons::REP_HR_EX);

                    $otherRow = new SRegistryRow();
                    $otherRow->idEmployee = $idEmployee;
                    $otherRow->numEmployee = $oEmployee->num_employee;
                    $otherRow->employee = $oEmployee->name;

                    $otherRow = SDataProcess::setDates($result, $otherRow, $sDate);

                    $otherRow->hasChecks = false;
                    if ($otherRow->workable) {
                        $otherRow->comments = $otherRow->comments."Sin checadas. ";
                    }
                    // if (! $otherRow->hasSchedule) {
                    //     $comments = $otherRow->comments;
                    //     $newComments = str_replace("Sin horario.", "", $comments);
                    //     $otherRow->comments = $newComments."No laboral. ";
                    // }
                    // else {
                        // $otherRow->comments = $otherRow->comments."Sin checadas. ";
                    // }

                    $otherRow->inDateTime = $sDate;
                    $otherRow->outDateTime = $sDate;

                    $lRows[] = $otherRow;

                    if ($bug) {
                        $registries = $regTemp;
                        goto toAbsences;
                    }
                }
            }

            if (! $isNew) {
                if ($newRow->inDate == $sEndDate) {
                    $registry = (object) [
                        'type_id' => \SCons::REG_IN,
                        'time' => '12:00:00',
                        'date' => $newRow->inDate,
                        'employee_id' => $idEmployee
                    ];

                    $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, clone $lWorkshifts, $sStartDate, $sEndDate);
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
                    }

                    if ($again) {
                        if ($fRegistry != null) {
                            $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $fRegistry, $lAssigns, clone $lWorkshifts, $sStartDate, $sEndDate);
                        }
                        else {
                            $theRow = SDataProcess::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, clone $lWorkshifts, $sStartDate, $sEndDate);
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
    private static function manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, $qWorkshifts, $sStartDate, $sEndDate)
    {
        if ($isNew) {
            $newRow = new SRegistryRow();
            $newRow->idEmployee = $idEmployee;
            $newRow->numEmployee = $registry->num_employee;
            $newRow->employee = $registry->name;
        }

        $newRow->hasAssign = $lAssigns != null;
        $hasAssign = $newRow->hasAssign;
        $again = false;
        $oFoundRegistry = null;
        $isOut = false;

        if ($registry->type_id == \SCons::REG_OUT) {
            if ($hasAssign) {
                $result = SDelayReportUtils::processRegistry($lAssigns, $registry, \SCons::REP_HR_EX);
            }
            else {
                $result = SDelayReportUtils::checkSchedule(clone $qWorkshifts, $idEmployee, $registry, \SCons::REP_HR_EX);
            }

            // no tiene horario para el día actual
            if ($result == null) {
                if ($isNew) {
                    $isNew = false;
                    $again = true;
                    $newRow->inDate = $registry->date;
                    $newRow->inDateTime = $registry->date;
                    $newRow->comments = $newRow->comments."Sin entrada. ";
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

                        $newRow = SDataProcess::setDates($otherResult, $newRow);
                    }
                    else {
                        $newRow->outDate = $registry->date;
                        $newRow->outDateTime = $registry->date.' '.$registry->time;
                        $newRow->comments = $newRow->comments."Sin horario. ";
                        $newRow->hasSchedule = false;
                    }
                }
            }
            else {
                if ($newRow->inDate != null) {
                    if ($newRow->outDate == null) {
                        $newRow = SDataProcess::setDates($result, $newRow);

                        if (isset($registry->to_close) && $registry->to_close) {
                            $newRow->outDate = $newRow->inDate;
                            $newRow->outDateTime = $newRow->inDate;
                            $newRow->comments = $newRow->comments."Sin salida. ";
                        }
    
                        $isNew = true;
                    }
                }
                else {
                    //Sin entrada
                    $bFound = false;
                    if ($result->pinnedDateTime->toDateString() == $sStartDate) {
                        // buscar entrada un día antes
                        $oDateAux = clone $result->pinnedDateTime;
                        $oDateAux->subDay();
                        $oFoundRegistryI = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_IN);

                        if ($oFoundRegistryI != null) {
                            $newRow->sInDate = $oFoundRegistryI->date.' '.$oFoundRegistryI->time;
                            $newRow->inDate = $oFoundRegistryI->date;
                            $newRow->inDateTime = $oFoundRegistryI->date.' '.$oFoundRegistryI->time;
    
                            $isNew = false;
                            $again = true;
                            $bFound = true;
                        }
                    }

                    if (! $bFound) {
                        $newRow->outDate = $result->variableDateTime->toDateString();
                        $newRow->outDateTime = $result->variableDateTime->format('Y-m-d   H:i:s');
                        $newRow->outDateTimeSch = $result->pinnedDateTime->format('Y-m-d   H:i:s');
                        $newRow->cutId = SDelayReportUtils::getCutId($result);

                        $isNew = true;
                        $again = false;
                        $newRow->inDate = $registry->date;
                        $newRow->inDateTime = $registry->date;
                        $newRow->comments = $newRow->comments."Sin entrada".". ";
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

                    $isNew = false;
                }
                else {
                    // Sin salida
                    $bFound = false;
                    if ($registry->date == $sEndDate) {
                        // buscar salida un día después
                        $oDateAux = Carbon::parse($registry->date);
                        $oDateAux->addDay();
                        $oFoundRegistry = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_OUT);
                        
                        if ($oFoundRegistry != null) {
                            if ($oFoundRegistry->date == $oDateAux->toDateString()) {
                                $config = \App\SUtils\SConfiguration::getConfigurations();

                                $registryAux = (object) [
                                    'type_id' => \SCons::REG_OUT,
                                    'time' => $oFoundRegistry->time,
                                    'date' => $oFoundRegistry->date,
                                    'employee_id' => $idEmployee
                                ];

                                $sched = SDelayReportUtils::getSchedule($oDateAux->toDateString(), $oDateAux->toDateString(), $idEmployee, $registryAux, clone $qWorkshifts, \SCons::REP_HR_EX);
                                if ($sched != null && abs($sched->diffMinutes) <= $config->maxGapMinutes) {
                                    $isOut = true;
                                }
                            }

                            $isNew = false;
                            $bFound = true;
                            $again = true;
                        }
                    }

                    if (! $bFound) {
                        if ($hasAssign) {
                            $result = SDelayReportUtils::processRegistry($lAssigns, $registry, \SCons::REP_HR_EX);
                        }
                        else {
                            $result = SDelayReportUtils::checkSchedule(clone $qWorkshifts, $idEmployee, $registry, \SCons::REP_HR_EX);
                        }

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
                                    if ($oAux->name == "Noche") {
                                        $night = true;
                                    }
                                }
                                else {
                                    return 0;
                                }
                            }
                            if ($night) {
                                $newRow->outDateTimeSch = $result->pinnedDateTime->toDateTimeString();
                            }
                            else {
                                $result->pinnedDateTime->subDay();
                                $newRow->outDateTimeSch = $result->pinnedDateTime->toDateTimeString();
                            }
                        }

                        $newRow->outDate = $newRow->inDate;
                        $newRow->outDateTime = $newRow->inDate;
                        $newRow->comments = $newRow->comments."Sin salida".". ";
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
     * @param App\SUtils\SDateComparison $result
     * @param Object $oRow
     * @param string $sDate
     * 
     * @return Object $oRow
     */
    private static function setDates($result, $oRow, $sDate = null)
    {
        if ($result == null) {
            $oRow->outDate = $sDate;
            $oRow->inDate = $sDate;
            // $oRow->inDateTime = $sDate.' 00:00:00';
            // $oRow->outDateTime = $sDate.' 00:00:00';
            $oRow->inDateTime = $sDate;
            $oRow->outDateTime = $sDate;

            $oRow->comments = $oRow->comments."Sin horario. ";
            $oRow->hasSchedule = false;
        }
        else {
            $oRow->scheduleFrom = SDataProcess::getOrigin($result);

            if ($oRow->scheduleFrom == \SCons::FROM_ASSIGN && ! $result->auxScheduleDay->is_active) {
                $oRow->outDate = $result->variableDateTime->toDateString();
                $oRow->outDateTime = $result->variableDateTime->toDateTimeString();
                $oRow->comments = $oRow->comments."No laborable. ";
                $oRow->workable = false;

                return $oRow;
            }

            $oRow->outDate = $result->variableDateTime->toDateString();
            $oRow->outDateTime = $result->variableDateTime->toDateTimeString();
            $oRow->outDateTimeSch = $result->pinnedDateTime->toDateTimeString();
    
            $sInSchedule = SDelayReportUtils::getScheduleIn($result);
    
            $oRow->inDateTimeSch = $sInSchedule;
    
            $oRow->cutId = SDelayReportUtils::getCutId($result);
            // minutos configurados en la tabla
            $oRow->overDefaultMins = SDelayReportUtils::getExtraTime($result);
            // minutos por turnos de más de 8 horas
            $oRow->overScheduleMins = SDelayReportUtils::getExtraTimeBySchedule($result, $oRow->inDateTime, $oRow->inDateTimeSch,
                                                                                        $oRow->outDateTime, $oRow->outDateTimeSch);

            $oRow = SDataProcess::checkTypeDay($result, $oRow);
        }

        return $oRow;
    }

    /**
     * Retorna un valor dependiendo de donde fue obtenido el horario,
     * si desde los horarios asignados o los programados
     *
     * @param App\SUtils\SDateComparison $result
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
     * Asigna al renglón en base al tipo de día si es un día festivo, descanso, etc.
     *
     * @param App\SUtils\SDateComparison $result
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
    public static function addEventsDaysOffAndHolidays($lData53, $qWorkshifts)
    {
        foreach ($lData53 as $oRow) {
            if (! $oRow->workable) {
                continue;
            }
            if ($oRow->inDateTimeSch == null) {
                $sDt = Carbon::parse($oRow->inDateTime);
            }
            else {
                $sDt = Carbon::parse($oRow->inDateTimeSch);
            }

            $lAbsences = prePayrollController::searchAbsence($oRow->idEmployee, $sDt->toDateString());
                    
            if (sizeof($lAbsences) > 0) {
                foreach ($lAbsences as $absence) {
                    $key = explode("_", $absence->external_key);

                    $abs = [];
                    $abs['id_emp'] = $key[0];
                    $abs['id_abs'] = $key[1];
                    $abs['nts'] = $absence->nts;
                    $abs['type_name'] = $absence->type_name;
                    $oRow->others = $oRow->others."".$absence->type_name.". ";

                    $oRow->events[] = $abs;
                }
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
                $oRow->isHoliday++;
                $text = "Festivo";
                break;

            case \SCons::T_DAY_DAY_OFF:
                $oRow->isDayOff++;
                break;

            default:
                # code...
                break;
        }

        $oRow->others."".$text.". ";

        return $oRow;
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
    public static function addDelaysAndOverTime($lData, $aEmployeeOverTime)
    {
        foreach ($lData as $oRow) {
            if (! $oRow->hasChecks || ! $oRow->workable) {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;

                $oRow->overMinsTotal = 0;
                $oRow->extraHours = SDelayReportUtils::convertToHoursMins($oRow->overMinsTotal);

                continue;
            }

            if (! $oRow->hasSchedule) {
                $oRow = SDataProcess::determineSchedule($oRow);
            }

            // minutos de retardo
            $oRow->entryDelayMinutes = SDataProcess::getDelayMins($oRow->inDateTime, $oRow->inDateTimeSch);
            // minutos de salida anticipada
            $oRow->prematureOut = SDataProcess::getPrematureTime($oRow->outDateTime, $oRow->outDateTimeSch);
            if (SDataProcess::journeyCompleted($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch)) {
                if ($aEmployeeOverTime[$oRow->idEmployee]) {
                    // minutos extra trabajados y filtrados por bandera de "genera horas extra"
                    $oRow->overWorkedMins = SDataProcess::getOverTime($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch);
                }
            }
            else {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;
            }
            $mayBeOverTime = false;
            $cIn = SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $mayBeOverTime);
            if ($cIn) {
                $oRow->comments = $oRow->comments."Entrada atípica. ";
                $oRow->isAtypicalIn = true;
            }
            $mayBeOverTime = $aEmployeeOverTime[$oRow->idEmployee];
            $cOut = SDataProcess::isCheckSchedule($oRow->outDateTime, $oRow->outDateTimeSch, $mayBeOverTime);
            if ($cOut) {
                $oRow->comments = $oRow->comments."Salida atípica. ";
                $oRow->isAtypicalOut = true;
            }
            if ($cIn || $cOut) {
                $oRow->comments = $oRow->comments."Revisar horario. ";
                $oRow->isCheckSchedule = true;
            }

            if ($oRow->isAtypicalOut && $oRow->isAtypicalIn) {
                $oRow->overDefaultMins = 0;
            }

            // suma de minutos extra totales.
            $oRow->overMinsTotal = $oRow->overWorkedMins + $oRow->overDefaultMins + $oRow->overScheduleMins;
            $oRow->extraHours = SDelayReportUtils::convertToHoursMins($oRow->overMinsTotal);

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
     * 
     * @return boolean
     */
    public static function journeyCompleted($inDateTime, $inDateTimeSch, $outDateTime, $outDateTimeSch)
    {
        if ($inDateTime == null || $inDateTimeSch == null || $outDateTime == null || $outDateTimeSch == null) {
            return false;
        }
        
        $comparisonCheck = SDelayReportUtils::compareDates($inDateTime, $outDateTime);
        $comparisonSched = SDelayReportUtils::compareDates($inDateTimeSch, $outDateTimeSch);
        
        $config = \App\SUtils\SConfiguration::getConfigurations();

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
            return $comparison->diffMinutes > 0 && $comparison->diffMinutes > $config->maxGapMinutes;
        }
        
        return abs($comparison->diffMinutes) > $config->maxGapMinutes;
    }

    /**
     * Determina por las checadas de entrada y salida si el horario pertenece a alguno
     * de los que otorgan tiempo extra y los asigna al empleado junto con sus horas extra
     *
     * @param App\SUtils\SRegistryRow $oRow
     * 
     * @return App\SUtils\SRegistryRow
     */
    public static function determineSchedule($oRow)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $inDate = Carbon::parse($oRow->inDateTime)->toDateString();
        $outDate = Carbon::parse($oRow->outDateTime)->toDateString();
        $comparisonIn = SDelayReportUtils::compareDates($oRow->inDateTime, $inDate.' 14:30:00');
        $comparisonOut = SDelayReportUtils::compareDates($oRow->outDateTime, $outDate.' 22:30:00');

        $oRow->isOnSchedule = true;
        
        if (abs($comparisonIn->diffMinutes) <= $config->maxGapMinutes && abs($comparisonOut->diffMinutes) <= $config->maxGapMinutes) {
            $oRow->inDateTimeSch = $inDate.' 14:30:00';
            $oRow->outDateTimeSch = $outDate.' 22:30:00';
            $oRow->overDefaultMins = 30;
            return $oRow;
        }

        $comparisonIn = SDelayReportUtils::compareDates($oRow->inDateTime, $inDate.' 18:30:00');
        $comparisonOut = SDelayReportUtils::compareDates($oRow->outDateTime, $outDate.' 06:30:00');
        
        if (abs($comparisonIn->diffMinutes) <= $config->maxGapMinutes && abs($comparisonOut->diffMinutes) <= $config->maxGapMinutes) {
            $oRow->inDateTimeSch = $inDate.' 18:30:00';
            $oRow->outDateTimeSch = $outDate.' 06:30:00';
            $oRow->overDefaultMins = 300;
            return $oRow;
        }

        $comparisonIn = SDelayReportUtils::compareDates($oRow->inDateTime, $inDate.' 22:30:00');
        $comparisonOut = SDelayReportUtils::compareDates($oRow->outDateTime, $outDate.' 06:30:00');
        
        if (abs($comparisonIn->diffMinutes) <= $config->maxGapMinutes && abs($comparisonOut->diffMinutes) <= $config->maxGapMinutes) {
            $oRow->inDateTimeSch = $inDate.' 22:30:00';
            $oRow->outDateTimeSch = $outDate.' 06:30:00';
            $oRow->overDefaultMins = 60;
            return $oRow;
        }

        $comparisonIn = SDelayReportUtils::compareDates($oRow->inDateTime, $inDate.' 06:30:00');
        $comparisonOut = SDelayReportUtils::compareDates($oRow->outDateTime, $outDate.' 18:30:00');
        
        if (abs($comparisonIn->diffMinutes) <= $config->maxGapMinutes && abs($comparisonOut->diffMinutes) <= $config->maxGapMinutes) {
            $oRow->inDateTimeSch = $inDate.' 06:30:00';
            $oRow->outDateTimeSch = $outDate.' 18:30:00';
            $oRow->overDefaultMins = 240;
            return $oRow;
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
    public static function addAbsences($lData, $aEmployeeBen)
    {
        foreach ($lData as $oRow) {
            $hasAbs = false;
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
                    }
                    break;
                
                default:
                    # code...
                    break;
            }

            if ($hasAbs) {
                $oRow->hasAbsence = true;
                $oRow->comments = $oRow->comments."Falta. ";
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
        foreach ($lData as $oRow) {
            if (! $oRow->hasChecks || ! $oRow->workable) {
                continue;
            }

            if ($oRow->outDate != null) {
                if (SDateTimeUtils::dayOfWeek($oRow->outDate) == Carbon::SUNDAY) {
                    $oRow->isSunday++;
                }
            }
        }

        return $lData;
    }

    /**
     * Procesa las checadas de un día en específico y determina si 
     * el empleado checó más de una vez en el momento
     *
     * @param array[SRegistry] $lCheks
     * 
     * @return array[SRegistry] con las checadas válidas
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
                    // diferencia entre cheinIns es mucha se agregan las 2
                    $chekDate = $check->date.' '.$check->time;
                    $chekODate = $oCheckIn->date.' '.$oCheckIn->time;
                    $comparison = SDelayReportUtils::compareDates($chekDate, $chekODate);
                    if (abs($comparison->diffMinutes) >= 360) {
                        $lNewChecks[] = $oCheckIn;
                        $oCheckIn = $check;
                    }
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
                    }
                }
                
                $oCheckOut = $check;
            }
        }

        if ($oCheckIn != null) {
            $lNewChecks[] = $oCheckIn;
        }

        if ($oCheckOut != null) {
            $lNewChecks[] = $oCheckOut;
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

        if ($result == null) {
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

        $inDateTime = $sDate.' '.$inTime;
        $outDateTime = $sDate.' '.$outTime;
        $originalChecks = clone $lCheks;
        foreach ($lCheks as $oCheck) {
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
                $check->type_id = \SCons::REG_IN;
            }
            else {
                if (abs($comparisonOut->diffMinutes) <= $config->maxGapSchedule) {
                    $check->type_id = \SCons::REG_OUT;
                }
            }

            $lNewChecks[] = $check;
        }

        return SDataProcess::filterDoubleCheks($lNewChecks);
    }

    private static function manageOneCheck($sDate, $idEmployee, $registries, $lWorkshifts)
    {
        $oRegistry = $registries[0];
        $result = SDelayReportUtils::getSchedule($sDate, $sDate, $idEmployee, $oRegistry, clone $lWorkshifts, \SCons::REP_HR_EX);

        if ($result == null) {
            return 0;
        }

        $sdate = $registries[0]->date;
        $entry = "";
        $dept = "";
        if ($result->auxWorkshift != null) {
            $entry = $result->auxWorkshift->entry;
            $dept = $result->auxWorkshift->departure;
        }
        if ($result->auxScheduleDay != null) {
            $entry = $result->auxScheduleDay->entry;
            $dept = $result->auxScheduleDay->departure;
        }

        $comparison = null;
        $config = \App\SUtils\SConfiguration::getConfigurations();
        // if ($registries[0]->type_id == \SCons::REG_OUT) {
        //     $comparison = SDelayReportUtils::compareDates($sdate.' '.$oRegistry->time, $sdate.' 06:30:00');

        //     return (abs($comparison->diffMinutes) <= $config->maxGapMinutes) ? 1 : 0;
        // }
        if ($registries[0]->type_id == \SCons::REG_IN) {
            $comparison = SDelayReportUtils::compareDates($sdate.' '.$oRegistry->time, $sdate.' 22:30:00');
            
            if (abs($comparison->diffMinutes) <= $config->maxGapMinutes) {
                $oDateAux = Carbon::parse($sdate);
                $oDateAux->subDay();
                $oFoundRegistry = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_IN);

                if ($oFoundRegistry == null) {
                    return 2;
                }
                else {
                    $comparison = SDelayReportUtils::compareDates($oFoundRegistry->date.' '.$oFoundRegistry->time, $oDateAux->toDateString().' 22:30:00');

                    if (abs($comparison->diffMinutes) <= $config->maxGapMinutes) {
                        return 0;
                    }
                    else {
                        return 2;
                    }
                }
            }
        }

        return 0;
    }
}

?>