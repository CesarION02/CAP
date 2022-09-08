<?php namespace App\SUtils;

use Carbon\Carbon;

class SDateTimeUtils {

    /**
     * gets the date of the last day of month and year received
     *
     * @param  integer $iMonth
     * @param  integer $iYear
     * @param  string $sFormat 'd-m-Y' or 'Y-m-d'
     *
     * @return string date based in format received
     */
    public static function getLastDayOfMonth($iMonth = 0, $iYear = 0, $sFormat = '') {
        $day = date("d", mktime(0,0,0, $iMonth + 1, 0, $iYear));

        return date($sFormat, mktime(0,0,0, $iMonth, $day, $iYear));
    }
  
    /**
     * gets the date of the first day of month and year received
     *
     * @param  integer $iMonth
     * @param  integer $iYear
     * @param  string $sFormat 'd-m-Y' or 'Y-m-d'
     * @return string date based in format received
     */
    public static function getFirstDayOfMonth($iMonth = 0, $iYear = 0, $sFormat = '') {
        return date($sFormat, mktime(0,0,0, $iMonth, 1, $iYear));
    }

    /**
     * Regresa un entero correspondiente al día de la fecha:
     * 
     * var_dump(Carbon::SUNDAY);     // int(0)
     * var_dump(Carbon::MONDAY);     // int(1)
     * var_dump(Carbon::TUESDAY);    // int(2)
     * var_dump(Carbon::WEDNESDAY);  // int(3)
     * var_dump(Carbon::THURSDAY);   // int(4)
     * var_dump(Carbon::FRIDAY);     // int(5)
     * var_dump(Carbon::SATURDAY);   // int(6)
     *
     * @param [String o Carbon] $date
     * @return int día entero
     */
    public static function dayOfWeek($date) {
        if (is_string($date)) {
            $oDate = Carbon::parse($date);
        }
        else {
            $oDate = clone $date;
        }

        // Carbon::setWeekStartsAt(Carbon::FRIDAY);
        $day = ($oDate->toObject()->dayOfWeek);

        return $day;
    }

    public static function orderDate($date) {
        $dateAux = explode('-',$date);

        $newDate = ''.$dateAux[2].'/'.$dateAux[1].'/'.$dateAux[0];

        return $newDate;
    }

    /**
     * Determina el tiempo extra que se otorgará al empleado en base a las reglas establecidas.
     * Menos de 20 min, 0 horas extra.
     * Entre 20 y 49 Min, 0.5 horas
     * 50 min o más, 1 hora extra.
     *
     * @param integer $overMins
     * 
     * @return integer tiempo extra en minutos
     */
    public static function getExtraTimeByRules($overMins = 0) {
        $initialLimitHalf = 20;
        $initialLimitHour = 50;
        $finalLimitHour = 60;

        $completeHours = intdiv($overMins, 60);
        $halfHours = $overMins % 60;
        $auxHalfHours = 0;
        if ($initialLimitHour <= $halfHours && $halfHours <= $finalLimitHour) {
            $auxHalfHours = 1;
        }
        else if ($initialLimitHalf <= $halfHours && $halfHours < $initialLimitHour) {
            $auxHalfHours = 0.5;
        }

        $overMinsByRule = ($completeHours + $auxHalfHours) * 60;

        return $overMinsByRule;
    }
}

?>