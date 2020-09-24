<?php namespace App\SData;

use Carbon\Carbon;
use App\SUtils\SDelayReportUtils;

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
    public static function processOverTimeByOverJourney($lData, $sStartDate)
    {
        $idEmployee = 0;
        $currentDate = null;
        foreach ($lData as $oRow) {
            if ($idEmployee != $oRow->idEmployee) {
                $firstTime = true;
                $journeyMin = 0;
                $currentDate = $sStartDate;
                $idEmployee = $oRow->idEmployee;
            }
            
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

                        $oRow->overScheduleMins += $workedTime->diffMinutes;
                        $oRow->overMinsTotal += $workedTime->diffMinutes;
                        $oRow->isOverJourney = true;
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
                        }
                        else {
                            // si no, solo se acumulan los minutos trabajados en este rango de tiempo
                            $journeyMin += $workedTime->diffMinutes;
                        }
                    }

                    $oRow->isDayRepeated = true;
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

                goto again;
            }
        }

        return $lData;
    }
}