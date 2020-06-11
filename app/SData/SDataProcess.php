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
                $registries = SDataProcess::filterDoubleCheks($registries);

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
                            'type_id' => 2,
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

                    $result = SDelayReportUtils::checkSchedule(clone $lWorkshifts, $idEmployee, $registry, \SCons::REP_HR_EX);

                    $otherRow = new SRegistryRow();
                    $otherRow->idEmployee = $idEmployee;
                    $otherRow->numEmployee = $oEmployee->num_employee;
                    $otherRow->employee = $oEmployee->name;

                    $otherRow = SDataProcess::setDates($result, $otherRow, $sDate);

                    $otherRow->hasChecks = false;
                    $otherRow->comments = $otherRow->comments."Sin checadas. ";

                    if ($result != null) {
                        $otherRow->inDateTime = $sDate.' 00:00:00';
                        $otherRow->outDateTime = $sDate.' 00:00:00';
                    }

                    $lRows[] = $otherRow;
                }
            }

            if (! $isNew) {
                $lRows[] = $newRow;
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

        $hasAssign = $lAssigns != null;
        $again = false;
        $oFoundRegistry = null;

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
                    $newRow->comments = $newRow->comments."Falta entrada. ";
                }
                else {
                    $otherResult = SDelayReportUtils::getNearSchedule($registry->date, $registry->time, $idEmployee, clone $qWorkshifts);
                    $isNew = true;

                    if ($otherResult != null) {
                        $newRow = SDataProcess::setDates($result, $newRow);
                    }
                    else {
                        $newRow->outDate = $registry->date;
                        $newRow->outDateTime = $registry->date.' '.$registry->time;
                        $newRow->comments = $newRow->comments."Sin horario".". ";
                        $newRow->hasSchedule = false;
                    }
                }
            }
            else {
                if ($newRow->inDate != null) {
                    if ($newRow->outDate == null) {
                        $newRow = SDataProcess::setDates($result, $newRow);

                        if (isset($registry->to_close) && $registry->to_close) {
                            $newRow->outDate = null;
                            $newRow->outDateTime = null;
                            $newRow->comments = $newRow->comments."Falta salida. ";
                        }
    
                        $isNew = true;
                    }
                }
                else {
                    //falta entrada
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
                        $newRow->comments = $newRow->comments."Falta entrada".". ";
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
                    // falta salida
                    $bFound = false;
                    if ($registry->date == $sEndDate) {
                        // buscar salida un día después
                        $oDateAux = Carbon::parse($registry->date);
                        $oDateAux->addDay();
                        $oFoundRegistry = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_OUT);
                        if ($oFoundRegistry != null) {
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

                        $newRow->comments = $newRow->comments."Falta salida".". ";
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

        return $response;
    }

    private static function setDates($result, $oRow, $sDate = null)
    {
        if ($result == null) {

            $oRow->outDate = $sDate;
            $oRow->inDate = $sDate;
            $oRow->inDateTime = $sDate.' 00:00:00';
            $oRow->outDateTime = $sDate.' 00:00:00';

            $oRow->comments = $oRow->comments."Sin horario. ";
            $oRow->hasSchedule = false;
        }
        else {
            $oRow->outDate = $result->variableDateTime->toDateString();
            $oRow->outDateTime = $result->variableDateTime->toDateTimeString();
            $oRow->outDateTimeSch = $result->pinnedDateTime->toDateTimeString();
    
            $sInSchedule = SDelayReportUtils::getScheduleIn($result);
    
            $oRow->inDateTimeSch = $sInSchedule;
    
            $oRow->cutId = SDelayReportUtils::getCutId($result);
            // minutos configurados en la tabla
            $oRow->overDefaultMins = SDelayReportUtils::getExtraTime($result);
            // minutos por turnos de más de 8 horas
            $oRow->overScheduleMins = SDelayReportUtils::getExtraTimeBySchedule($result);

            $oRow = SDataProcess::checkTypeDay($result, $oRow);
        }

        return $oRow;
    }

    public static function checkTypeDay($result, $oRow)
    {
        if ($result->auxWorkshift != null) {
            $oRow->isTypeDayChecked = true;

            if ($result->auxWorkshift->type_day_id == \SCons::T_DAY_NORMAL) {
                return $oRow;
            }

            switch ($result->auxWorkshift->type_day_id) {
                case \SCons::T_DAY_INHABILITY:
                    $oRow->dayInhability++;
                    break;

                case \SCons::T_DAY_VACATION:
                    $oRow->dayVacations++;
                    break;

                case \SCons::T_DAY_HOLIDAY:
                    $oRow->isHoliday++;
                    break;

                case \SCons::T_DAY_DAY_OFF:
                    $oRow->isDayOff++;
                    break;

                default:
                    # code...
                    break;
            }
        }
        
        return $oRow;
    }

    public static function addEventsDaysOffAndHolidays($lData53, $qWorkshifts)
    {
        foreach ($lData53 as $oRow) {
            $lAbsences = prePayrollController::searchAbsence($oRow->idEmployee, $oRow->inDate);
                    
            if (sizeof($lAbsences) > 0) {
                foreach ($lAbsences as $absence) {
                    $key = explode("_", $absence->external_key);

                    $abs = [];
                    $abs['id_emp'] = $key[0];
                    $abs['id_abs'] = $key[1];
                    $abs['nts'] = $absence->nts;
                    $oRow->comments = $oRow->comments."".$absence->nts.". ";

                    $oRow->events[] = $abs;
                }
            }

            if ($oRow->isTypeDayChecked) {
                continue;
            }

            $lWorks = clone $qWorkshifts;
            $events = SDelayReportUtils::checkEvents($lWorks, $oRow->idEmployee, $oRow->inDate);

            if ($events == null) {
                continue;
            }

            foreach ($events as $event) {
                if ($event->type_day_id == \SCons::T_DAY_NORMAL) {
                    continue;
                }

                switch ($event->type_day_id) {
                    case \SCons::T_DAY_INHABILITY:
                        $oRow->dayInhability++;
                        break;

                    case \SCons::T_DAY_VACATION:
                        $oRow->dayVacations++;
                        break;

                    case \SCons::T_DAY_HOLIDAY:
                        $oRow->isHoliday++;
                        break;

                    case \SCons::T_DAY_DAY_OFF:
                        $oRow->isDayOff++;
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }

        $lData53_2 = $lData53;

        return $lData53_2;
    }

    public static function addDelaysAndOverTime($lData, $aEmployeeOverTime)
    {
        foreach ($lData as $oRow) {
            if (! $oRow->hasChecks) {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;

                $oRow->overMinsTotal = 0;
                $oRow->extraHours = SDelayReportUtils::convertToHoursMins($oRow->overMinsTotal);

                continue;
            }

            // minutos de retardo
            $oRow->entryDelayMinutes = SDataProcess::getDelayMins($oRow->inDateTime, $oRow->inDateTimeSch);
            // minutos de salida anticipada
            $oRow->prematureOut = SDataProcess::getPrematureTime($oRow->outDateTime, $oRow->outDateTimeSch);
            if (SDataProcess::journeyCompleted($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch)) {
                // minutos extra trabajados y filtrados por bandera de "genera horas extra"
                if ($aEmployeeOverTime[$oRow->idEmployee]) {
                    $oRow->overWorkedMins = SDataProcess::getOverTime($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch);
                }
            }
            else {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;
            }
            
            // suma de minutos extra totales.
            $oRow->overMinsTotal = $oRow->overWorkedMins + $oRow->overDefaultMins + $oRow->overScheduleMins;
            $oRow->extraHours = SDelayReportUtils::convertToHoursMins($oRow->overMinsTotal);

            if (SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch)) {
                $oRow->comments = $oRow->comments."Revisar horario. ";
                $oRow->isCheckSchedule = true;
            }
        }

        return $lData;
    }

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

    public static function isCheckSchedule($inDateTime, $inDateTimeSch, $outDateTime, $outDateTimeSch)
    {
        if ($inDateTime == null || $inDateTimeSch == null) {
            return false;
        }

        $comparisonIn = SDelayReportUtils::compareDates($inDateTime, $inDateTimeSch);
        $config = \App\SUtils\SConfiguration::getConfigurations();

        if (abs($comparisonIn->diffMinutes) > $config->maxGapMinutes) {
            return true;
        }

        if ($outDateTime == null || $outDateTimeSch == null) {
            return false;
        }

        $comparisonOut = SDelayReportUtils::compareDates($outDateTime, $outDateTimeSch);
        
        return abs($comparisonOut->diffMinutes) > $config->maxGapMinutes;
    }

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
                                    && $oRow->dayVacations == 0) {
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

    public static function addSundayPay($lData)
    {
        foreach ($lData as $oRow) {
            if (! $oRow->hasChecks) {
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
}

?>