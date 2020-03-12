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
}

?>