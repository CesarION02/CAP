<?php 
namespace App\SUtils;

use Carbon\Carbon;
use App\SUtils\SDateUtils;

class SCheckDaysVobo {
    public static function checkDays($employee, $today, $ini_date, $end_date = null) {
        try {
            $isInRange = true;
            $lDays = [];
            $message = '';
            $way_pay = $employee->way_pay_id;

            $config = \App\SUtils\SConfiguration::getConfigurations();
    
            if (!$end_date) {
                $end_date = $ini_date;
            }
    
            $dt_cut = '';
            if ($way_pay == 2) {
                $arrNumberWeek = SDateUtils::getNumberOfDate($ini_date, $way_pay);
                $arrDatesWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberWeek[0], $arrNumberWeek[1], $way_pay);
    
                if($arrDatesWeek[1] == null){
                    return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                }
                
                $dt_cut = $arrDatesWeek[1];
                $oCut = Carbon::parse($dt_cut);
                if ($oCut->dayOfWeek == 5 || $oCut->dayOfWeek == 6) {
                    $cut = Carbon::parse($dt_cut)->add('week', 1)->startOfWeek();
                    $dt_cut = $cut->format('Y-m-d');
                }
                $days = $config->daysToCloseWeekVobo;
            } else if ($way_pay == 1) {
                $arrNumberBiWeek = SDateUtils::getNumberOfDate($ini_date, $way_pay);
                $arrDatesBiWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberBiWeek[0], $arrNumberBiWeek[1], $way_pay);
    
                if($arrDatesBiWeek[1] == null){
                    return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                }
    
                $dt_cut = $arrDatesBiWeek[1];
                $oCut = Carbon::parse($dt_cut);
                if ($oCut->dayOfWeek == 5 || $oCut->dayOfWeek == 6) {
                    $cut = Carbon::parse($dt_cut)->add('week', 1)->startOfWeek();
                    $dt_cut = $cut->format('Y-m-d');
                }
                $days = $config->daysToCloseBiWeekVobo;
            }
    
            $oDt_cut = Carbon::parse($dt_cut)->add('day', $days)->endOfDay();
            $oToday = Carbon::parse($today)->endOfDay();
    
            if ($oToday->gt($oDt_cut)) {
                $isInRange = false;
                $message = 'La prenómina ya no se puede modificar porque ya pasó la fecha de revisión.';
            }
            
            if (!$isInRange) {
                $rangeDays = Carbon::parse($ini_date)->diffInDays($end_date);
                if ($rangeDays < 1) {
                    $rangeDays = 1;
                }
                $oDate = Carbon::parse($ini_date);
                for ($i = 1; $i <= $rangeDays; $i++) {
                    if ($oDate->gt($oDt_cut)) {
                        $lDays[] = $oDate->format('Y-m-d');
                    }
                    $oDate->add('day', 1);
                }
            }
    
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['isInRange' => false, 'days' => $lDays, 'message' => $th->getMessage()]);
        }

        return json_encode(['isInRange' => $isInRange, 'days' => $lDays, 'message' => $message]);
    }
}