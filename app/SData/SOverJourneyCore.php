<?php namespace App\SData;

use Carbon\Carbon;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SDateTimeUtils;

class SOverJourneyCore {

    /**
     * Agrega el tiempo extra cuando un empleado ya ha trabajado más de 8 horas el mismo día
     * en diferentes jornadas
     *
     * @param array $lData
     * @param string $sStartDate
     * 
     * @return array $lData
     */
    public static function processOverTimeByOverJourney($lData, $sStartDate, $comments = null)
    {
        $idEmployee = 0;
        $currentDate = null;
        $config = \App\SUtils\SConfiguration::getConfigurations();
        foreach ($lData as $oRow) {
            if ($idEmployee != $oRow->idEmployee) {
                $firstTime = true;
                $journeyMin = 0;
                $currentDate = $sStartDate;
                $idEmployee = $oRow->idEmployee;
            }

            // \Log::info($oRow->employee.' '.$oRow->inDateTime);
            
            again:
            if (($oRow->overtimeCheckPolicy == \SCons::OVERTIME_CHECK_POLICY_OUT && $currentDate == $oRow->outDate) || 
                ($oRow->overtimeCheckPolicy == \SCons::OVERTIME_CHECK_POLICY_IN && $currentDate == $oRow->inDate)) {
                /**
                 * Si no tiene checadas o le falta alguna se omite el proceso ya que no puede generar horas extra
                 * en su ausencia.
                 */
                if (!$oRow->hasChecks || !$oRow->hasCheckIn || ! $oRow->hasCheckOut) {
                    continue;
                }

                $dtStart = Carbon::parse($oRow->outDate.' 00:00');
                $dtOut = Carbon::parse($oRow->outDateTime);

                $wkedTime = SDelayReportUtils::compareDates($dtStart, $dtOut);

                if ($wkedTime->diffMinutes <= $config->minMinsOverNextDay) {
                    continue;
                }

                /**
                 * El tiempo trabajado se toma desde la hora programada de entrada si llegó antes de esta o
                 * desde la hora real de entrada si llegó después de la programada
                 */
                if ($oRow->inDateTimeSch > $oRow->inDateTime) {
                    $in = $oRow->inDateTimeSch;
                }
                else {
                    $in = $oRow->inDateTime;
                }

                $workedTime = SDelayReportUtils::compareDates($in, $oRow->outDateTime);

                /**
                 * Si es el primer renglón del día actual
                 */
                if ($firstTime) {
                    // si ya completó la jornada se ponen las 8 horas
                    if ($oRow->hasWorkedJourney8hr) {
                        $journeyMin = 480;
                    }
                    else {
                        // si no, solo se acumulan los minutos trabajados en este rango de tiempo
                        $journeyMin = $workedTime->diffMinutes;
                    }

                    $firstTime = false;
                }
                else {
                    if ($journeyMin == 480) {
                        /**
                         * Si ya se completó la jornada se agregan todos los minutos trabajados como horas extra
                         * se redondea a 8 horas en caso de faltar pocos minutos para completarlos
                         */
                        if (480 - $workedTime->diffMinutes > 0 && 480 - $workedTime->diffMinutes < 20) {
                            $workedTime->diffMinutes = 480;
                        }

                        $oRow->overMinsTotal -= $oRow->overDefaultMins;
                        $oRow->overDefaultMins = 0;

                        $oRow->overScheduleMins += $workedTime->diffMinutes;
                        $oRow->overMinsTotal += $workedTime->diffMinutes;
                        $oRow->isOverJourney = true;
                        // quitar retardos en segundo turno
                        $oRow->entryDelayMinutes = 0;
                        $oRow->comments = str_replace("Retardo.", "", $oRow->comments);
                    }
                    else {
                        /**
                         * Si los minutos acumulados + los minutos trabajados de la jornada actual suman más de 8 horas
                         * entonces se completa la jornada y los minutos sobrantes se agregan como tiempo extra
                         */
                        if ($journeyMin + $workedTime->diffMinutes > 480) {
                            $toRest = 480 - $journeyMin;
                            $extra = $workedTime->diffMinutes - $toRest;
                            $oRow->overScheduleMins += $extra;
                            $oRow->overMinsTotal += $extra;
                            $journeyMin = 480;

                            $oRow->overMinsTotal -= $oRow->overDefaultMins;
                            $oRow->overDefaultMins = 0;
                            // quitar retardos en segundo turno
                            $oRow->entryDelayMinutes = 0;
                            $oRow->comments = str_replace("Retardo.", "", $oRow->comments);
                        }
                        else {
                            // si no, solo se acumulan los minutos trabajados en este rango de tiempo
                            $journeyMin += $workedTime->diffMinutes;
                        }
                    }

                    $oRow->isDayRepeated = true;
                    if($oRow->isDayRepeated){
                        if($comments != null){
                            if($comments->where('key_code','isDayRepeated')->first()['value']){
                                $oRow->isDayChecked = true;
                            }
                        }
                    }
                }
            }
            else {
                /**
                 * Se suma un día a la fecha actual para continuar con la comparación
                 */
                $oDate = Carbon::parse($currentDate);
                $oDate->addDay();

                $currentDate = $oDate->toDateString();
                $firstTime = true;
                if ($currentDate <= $oRow->outDate) {
                    goto again;
                }
            }
            if((($oRow->overWorkedMins + $oRow->overMinsByAdjs) >= 20) || (($oRow->overScheduleMins + $oRow->overMinsByAdjs) >= 60)){
                if($comments != null){
                    if($comments->where('key_code','overWorkedMins')->first()['value']){
                        $oRow->isDayChecked = true;
                    }
                }
            }
        }

        return $lData;
    }

    /**
     * Si un empleado trabaja en un día menos de los minutos configurados como rango mínimo se le otorgarán como minutos extra,
     * Es decir, si un empleado en un día solo trabajo dos horas y el tiempo configurado es 4 horas, esas dos horas trabajas se le darán
     * como tiempo extra y el día será puesto como descanso
     * Implementar funcionalidad para que el revisor opte por pagar como tiempo extra o como día de descanso trabajado las jornadas 
     * registradas en días no laborable; funcionalidad configurable mediante parámetro de forma general. 
     * Ante la ausencia de configuración general, 
     * el sistema deberá funcionar como hasta ahora: pagará tiempo extra si, y solo si, al empleado en cuestión se le paga siempre el tiempo extra (sflores).
     *
     * @param collection $lData
     * 
     * @return collection $lData
     */
    public static function overtimeByIncompleteJourney($sStartDate, $sEndDate, $lData, $aEmployeeOverTime)
    {
        $idEmployee = 0;
        $currentDate = null;
        $previousDate = null;
        $workedMinutes = 0;
        $config = \App\SUtils\SConfiguration::getConfigurations();
        // Obtiene los empleados contenidos en $lData
        $employees = array_unique(collect($lData)->pluck('idEmployee')->toArray());
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

        // recorre los empleados contenidos en $lData
        foreach ($employees as $idEmp) {
            if ($aEmployeeOverTime[$idEmp] == \SCons::ET_POL_NEVER || 
                $aEmployeeOverTime[$idEmp] == \SCons::ET_POL_SOMETIMES) {
                continue;
            }

            // recorre día a día para obtener los minutos trabajados
            foreach ($aDates as $sDate) {
                $rows = collect($lData)->where('idEmployee', $idEmp)
                                        ->where('outDate', $sDate)
                                        ->where('workable', false)
                                        ->where('hasChecks', true)
                                        ->where('hasCheckIn', true)
                                        ->where('hasCheckOut', true)
                                        ->toArray();
                
                if (count($rows) > 0) {
                    // suma del tiempo trabajado en minutos
                    $workedMins = 0;
                    foreach ($rows as $idx => $row) {
                        $workedTime = SDelayReportUtils::compareDates($row->inDateTime, $row->outDateTime);
                        $workedMins += $workedTime->diffMinutes;
                    }

                    // si el tiempo trabajado es menor al máximo de tiempo configurado
                    if ($workedMins < $config->maxOvertimeJourneyMinutes && $workedMins > 0) {
                        $lData[$idx]->overWorkedMins += $workedMins;
                        $lData[$idx]->overMinsTotal += $workedMins;

                        // si el día es domingo quita la prima
                        if (SDateTimeUtils::dayOfWeek($lData[$idx]->outDate) == Carbon::SUNDAY) {
                            $lData[$idx]->isSunday = 0;
                        }
                    }
                }
            }
        }

        return $lData;
    }
}