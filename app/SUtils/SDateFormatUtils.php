<?php namespace App\SUtils;

use Carbon\Carbon;

class SDateFormatUtils {
    public const days = ['Dom.', 'Lun.', 'Mar.', 'Mié.', 'Jue.', 'Vie.', 'Sáb.'];
    public const daysComplete = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    public const daysMin = ['dom.', 'lun.', 'mar.', 'mié.', 'jue.', 'vie.', 'sáb.'];
    public const months = ['', 'Ene.', 'Feb.', 'Mar.', 'Abr.', 'May.', 'Jun.', 'Jul.', 'Ago.', 'Sep.', 'Oct.', 'Nov.', 'Dic.'];
    public const monthsComplete = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    public const monthsAux = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public static function formatDate($sDate, $format){
        try {
            $oDate = Carbon::parse($sDate);
            switch ($format) {
                case 'ddd D-M-Y':
                    $date = dateUtils::daysMin[$oDate->dayOfWeek].' '.$oDate->format('d').'-'.dateUtils::months[$oDate->month].'-'.$oDate->format('Y');
                    break;
                case 'ddd D-m-Y':
                    $date = dateUtils::daysMin[$oDate->dayOfWeek].' '.$oDate->format('d').'-'.dateUtils::monthsAux[$oDate->month].'-'.$oDate->format('Y');
                    break;
                case 'D-m-Y':
                    $date = $oDate->format('d').'-'.dateUtils::monthsAux[$oDate->month].'-'.$oDate->format('Y');
                    break;
                case 'DDD D-M-Y':
                    $date = dateUtils::days[$oDate->dayOfWeek].' '.$oDate->format('d').'-'.dateUtils::months[$oDate->month].'-'.$oDate->format('Y');
                    break;
                case 'DDD D-m-Y':
                    $date = dateUtils::days[$oDate->dayOfWeek].' '.$oDate->format('d').'-'.dateUtils::monthsAux[$oDate->month].'-'.$oDate->format('Y');
                    break;
                case 'D/m/Y dddd':
                    $date = $oDate->format('d').'/'.dateUtils::monthsAux[$oDate->month].'/'.$oDate->format('Y').' ('.dateUtils::daysComplete[$oDate->dayOfWeek].')';
                    break;
                case 'D/mm/Y':
                    $date = $oDate->format('d').'/'.$oDate->format('m').'/'.$oDate->format('Y');
                    break;
                case 'D-M-Y':
                    $date = $oDate->format('d').'-'.dateUtils::monthsAux[$oDate->month].'-'.$oDate->format('Y');
                    break;
                case 'dd-M-Y':
                    $date = $oDate->format('d').'-'.dateUtils::months[$oDate->month].'-'.$oDate->format('Y');
                    break;
                default:
                    $date = $oDate->format($format);
                    break;
            }
        } catch (\Throwable $th) {
            return "Fecha invalida.";
        }

        return $date;
    }

    public static function datesToString($sDateIni, $sDateEnd, $format = 'M'){
        $oDateIni = Carbon::parse($sDateIni);
        $oDateEnd = Carbon::parse($sDateEnd);

        $sDate = '';
        if($oDateIni->month == $oDateEnd->month && $oDateIni->year == $oDateEnd->year){
            $sDate = $oDateIni->format('d').
                    ' al '.
                    $oDateEnd->format('d').
                    ' de '.
                    ($format == 'M' ? 
                        mb_strtolower(dateUtils::monthsComplete[$oDateEnd->month]) : 
                            mb_strtolower(dateUtils::monthsAux[$oDateEnd->month])).
                    ' de '.
                    $oDateEnd->format('Y');

        }else if($oDateIni->month != $oDateEnd->month && $oDateIni->year == $oDateEnd->year){
            $sDate = $oDateIni->format('d').
                    ' de '.
                    ($format == 'M' ?
                        mb_strtolower(dateUtils::monthsComplete[$oDateIni->month]) :
                            mb_strtolower(dateUtils::monthsAux[$oDateIni->month])).
                    ' al '.
                    $oDateEnd->format('d').
                    ' de '.
                    ($format == 'M' ?
                        mb_strtolower(dateUtils::monthsComplete[$oDateEnd->month])  :
                            mb_strtolower(dateUtils::monthsAux[$oDateEnd->month])).
                    ' de '.
                    $oDateEnd->format('Y');

        }else if($oDateIni->month != $oDateEnd->month && $oDateIni->year != $oDateEnd->year){
            $sDate = $oDateIni->format('d').
                    ' de '.
                    ($format == 'M' ?
                        mb_strtolower(dateUtils::monthsComplete[$oDateIni->month]) :
                            mb_strtolower(dateUtils::monthsAux[$oDateIni->month])).
                    ' de '.
                    $oDateIni->format('Y').
                    ' al '.
                    $oDateEnd->format('d').
                    ' de '.
                    ($format == 'M' ?
                        mb_strtolower(dateUtils::monthsComplete[$oDateEnd->month]) :
                            mb_strtolower(dateUtils::monthsAux[$oDateEnd->month])).
                    ' de '.
                    $oDateEnd->format('Y');
        }else{
            $sDate = "Fecha invalida.";
        }

        return $sDate;
    }
}