<?php namespace App\SData;

use App\SUtils\SDelayReportUtils;

class SChecksCore
{
    /**
     * Filtra las checadas repetidas ya sea de entrada o de salida que estén juntas y arma pares de checadas
     *
     * @param \Illuminate\Support\Collection $lChecks
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function filterRepeatedChecks($lChecks)
    {
        $oConfig = \App\SUtils\SConfiguration::getConfigurations();
        $lNewChecks = [];
        $oCheckIn = null;
        $oCheckOut = null;

        foreach ($lChecks as $check) {
            $check->type_id === \SCons::REG_IN ?
                self::handleCheckIn($oCheckOut, $oCheckIn, $lNewChecks, $check, $oConfig) :
                self::handleCheckOut($oCheckIn, $oCheckOut, $lNewChecks, $check, $oConfig);
        }

        if ($oCheckIn !== null) {
            $lNewChecks[] = $oCheckIn;
        }

        if ($oCheckOut !== null) {
            $lNewChecks[] = $oCheckOut;
        }

        /**
         * Log de las checadas omitidas de los empleados que registran varias veces entrada o salida
         */
        if (count($lChecks) !== count($lNewChecks)) {
            foreach ($lChecks as $indexCheck) {
                if (! in_array($indexCheck, $lNewChecks) && session()->has('logger')) {
                    session('logger')->log($indexCheck->employee_id, 'checada_omitida', $indexCheck->id, null, null, null);
                }
            }
        }

        return collect($lNewChecks);
    }

    private static function handleCheckIn(
        $oCheckOut,
        &$oCheckIn,
        array &$lNewChecks,
        $check,
        $oConfig
    ): void {
        if ($oCheckOut !== null) {
            $lNewChecks[] = $oCheckOut;
            $oCheckOut = null;
        }

        if ($oCheckIn === null) {
            $oCheckIn = $check;
        } else {
            $chekDate = "$check->date $check->time";
            $chekODate = "$oCheckIn->date $oCheckIn->time";
            $comparison = SDelayReportUtils::compareDates($chekDate, $chekODate);
            if (abs($comparison->diffMinutes) >= $oConfig->maxGapBetweenChecks) {
                $lNewChecks[] = $oCheckIn;
                $oCheckIn = $check;
            }
        }
    }

    private static function handleCheckOut(
        &$oCheckIn,
        &$oCheckOut,
        &$lNewChecks,
        $check,
        $oConfig
    ): void {
        if ($oCheckIn !== null) {
            $lNewChecks[] = $oCheckIn;
            $oCheckIn = null;
        }

        if ($oCheckOut !== null) {
            $chekDate = "$check->date $check->time";
            $chekODate = "$oCheckOut->date $oCheckOut->time";
            $comparison = SDelayReportUtils::compareDates($chekDate, $chekODate);
            if (abs($comparison->diffMinutes) >= $oConfig->maxGapBetweenChecks) {
                $lNewChecks[] = $oCheckOut;
                $oCheckOut = $check;
            }
        }
        else {
            $oCheckOut = $check;
        }
    }

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
            return self::filterRepeatedChecks($lCheks);
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

        $lRegistries = self::filterRepeatedChecks($lCheks);

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

        return self::filterRepeatedChecks($lNewChecks);
    }
}
