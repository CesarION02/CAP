<?php namespace App\SData;

use App\SUtils\SDelayReportUtils;

class SChecksCore
{
    
    /**
     * En base al horario del empleado determina si la checada
     * es de entrada o salida y retorna el arreglo con el tipo modificado
     *
     * @param \Illuminate\Support\Collection $lCheks
     * @param string $sDate
     * 
     * @return \Illuminate\Support\Collection
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

        if (is_null($result) || (! is_null($result->auxScheduleDay) && !$result->auxScheduleDay->is_active)) {
            return self::filterDoubleCheks($lCheks);
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

        $lRegistries = self::filterDoubleCheks($lCheks);

        $inDateTimeSch = $sDate.' '.$inTime;
        $outDateTimeSch = $sDate.' '.$outTime;
        $originalChecks = clone collect($lRegistries);
        foreach ($lRegistries as $oCheck) {
            $check = clone $oCheck;
            $registryDateTime = $check->date.' '.$check->time;

            $comparisonIn = SDelayReportUtils::compareDates($inDateTimeSch, $registryDateTime);
            $comparisonOut = SDelayReportUtils::compareDates($outDateTimeSch, $registryDateTime);

            // si ni entrada ni salida coinciden con el horario regresa las checadas originales
            if (abs($comparisonIn->diffMinutes) > $config->maxGapSchedule 
                && abs($comparisonOut->diffMinutes) > $config->maxGapSchedule) {
                $lNewChecks = $originalChecks;
                break;
            }

            if ($check->type_id == \SCons::REG_OUT) {
                // Si la checada está antes o igual que la hora de entrada
                if (($comparisonIn->diffMinutes <= 0 && abs($comparisonIn->diffMinutes) <= $config->maxGapScheTolerancePreIn) ||
                // Si la checada está después de la hora de entrada
                ($comparisonIn->diffMinutes > 0 && abs($comparisonIn->diffMinutes) <= $config->maxGapScheTolerancePosIn)) {
                    if (session()->has('logger')) {
                        /**
                         * Log de los empleados que checaron salida por entrada
                         */
                        session('logger')->log($check->employee_id, 'checada_cambio', $check->id, $check->type_id, null, null);
                    }
    
                    $check->type_id = \SCons::REG_IN;
                }
            }
            else {
                // Si la checada está antes de la hora de salida
                if (($comparisonOut->diffMinutes <= 0 && abs($comparisonOut->diffMinutes) <= $config->maxGapScheTolerancePreOut) ||
                // Si la checada está después de la hora de salida
                ($comparisonOut->diffMinutes > 0 && abs($comparisonOut->diffMinutes) <= $config->maxGapScheTolerancePosOut)) {
                    if (session()->has('logger')) {
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

        return self::filterDoubleCheks($lNewChecks);
    }

     /**
     * Procesa las checadas de un día en específico y determina si 
     * el empleado checó más de una vez en el momento.
     * Filtra las checadas repetidas ya sea de entrada o de salida que estén juntas y arma pares de checadas
     *
     * @param \Illuminate\Database\Eloquent\Collection $lCheks
     * 
     * @return \Illuminate\Support\Collection con las checadas válidas
     */
    public static function filterDoubleCheks($lCheks)
    {
        if (sizeof($lCheks) == 0) {
            return $lCheks;
        }

        $oConfig = \App\SUtils\SConfiguration::getConfigurations();
        $lNewChecks = array();

        $oCheckIn = null;
        $oCheckOut = null;
        foreach ($lCheks as $check) {
            if ($check->type_id == \SCons::REG_IN) {
                if (! is_null($oCheckOut)) {
                    $lNewChecks[] = $oCheckOut;
                }
                $oCheckOut = null;
                if (is_null($oCheckIn)) {
                    $oCheckIn = $check;
                }
                else {
                    // diferencia entre checkIns es mucha se agregan las 2
                    $chekDate = $check->date.' '.$check->time;
                    $chekODate = $oCheckIn->date.' '.$oCheckIn->time;
                    $comparison = SDelayReportUtils::compareDates($chekDate, $chekODate);
                    if (abs($comparison->diffMinutes) >= $oConfig->maxGapBetweenChecks) {
                        $lNewChecks[] = $oCheckIn;
                        $oCheckIn = $check;
                    }
                }
            }
            else {
                if (! is_null($oCheckIn)) {
                    $lNewChecks[] = $oCheckIn;
                }
                $oCheckIn = null;

                if (! is_null($oCheckOut)) {
                    $chekDate = $check->date.' '.$check->time;
                    $chekODate = $oCheckOut->date.' '.$oCheckOut->time;
                    $comparison = SDelayReportUtils::compareDates($chekDate, $chekODate);
                    if (abs($comparison->diffMinutes) >= $oConfig->maxGapBetweenChecks) {
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

        if (! is_null($oCheckIn)) {
            $lNewChecks[] = $oCheckIn;
        }

        if (! is_null($oCheckOut)) {
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

        return collect($lNewChecks);
    }
}
