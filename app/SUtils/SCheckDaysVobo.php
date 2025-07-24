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
            $type = '';
            $num = '';
            if ($way_pay == 2) {
                $arrNumberWeek = SDateUtils::getNumberOfDate($ini_date, $way_pay);
                $arrDatesWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberWeek[0], $arrNumberWeek[1], $way_pay);
    
                if($arrDatesWeek[1] == null){
                    return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                }

                $week = \DB::table('week_cut')
                                ->where('num', $arrNumberWeek[0])
                                ->where('year', $arrNumberWeek[1])
                                ->first();

                $config_close = \DB::table('payroll_closing_days')
                                    ->where('is_week', 1)
                                    ->where('week_id', $week->id)
                                    ->first();

                if (!is_null($config_close)) {
                    if ($config_close->applies) {
                        return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                    } else {
                        $days = $config_close->days + $config->daysToCloseWeekVobo;
                    }
                } else {
                    $days = $config->daysToCloseWeekVobo;
                }
                
                $dt_cut = $arrDatesWeek[1];
                $oCut = Carbon::parse($dt_cut);
                if ($oCut->dayOfWeek == 5 || $oCut->dayOfWeek == 6) {
                    $cut = Carbon::parse($dt_cut)->add('week', 1)->startOfWeek();
                    $dt_cut = $cut->format('Y-m-d');
                }
                
                $type = 'semanal';
                $num = $arrNumberWeek[0];
            } else if ($way_pay == 1) {
                $arrNumberBiWeek = SDateUtils::getNumberOfDate($ini_date, $way_pay);
                $arrDatesBiWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberBiWeek[0], $arrNumberBiWeek[1], $way_pay);
    
                if($arrDatesBiWeek[1] == null){
                    return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                }

                $biWeek = \DB::table('hrs_prepay_cut')
                                ->where('num', $arrNumberBiWeek[0])
                                ->where('year', $arrNumberBiWeek[1])
                                ->where('is_delete', 0)
                                ->first();

                $config_close = \DB::table('payroll_closing_days')
                                    ->where('is_biweek', 1)
                                    ->where('biweek_id', $biWeek->id)
                                    ->first();

                if (!is_null($config_close)) {
                    if ($config_close->applies) {
                        return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                    } else {
                        $days = $config_close->days + $config->daysToCloseBiWeekVobo;
                    }
                } else {
                    $days = $config->daysToCloseBiWeekVobo;
                }
    
                $dt_cut = $arrDatesBiWeek[1];
                $oCut = Carbon::parse($dt_cut);
                if ($oCut->dayOfWeek == 5 || $oCut->dayOfWeek == 6) {
                    $cut = Carbon::parse($dt_cut)->add('week', 1)->startOfWeek();
                    $dt_cut = $cut->format('Y-m-d');
                }
                
                $type = 'quincenal';
                $num = $arrNumberBiWeek[0];
            }

            $oDt_cut = Carbon::parse($dt_cut);
            for ($i = 0; $i < $days; $i++) {
                $oDt_cut = $oDt_cut->add('day', 1)->endOfDay();
                if ($oDt_cut->dayOfWeek == 5 || $oDt_cut->dayOfWeek == 6) {
                    $oDt_cut = Carbon::parse($dt_cut)->add('week', 1)->startOfWeek();
                }
            }
    
            $oToday = Carbon::parse($today)->endOfDay();
    
            if ($oToday->gt($oDt_cut)) {
                $isInRange = false;
                $message = 'La prenómina ' . $type . ' ' . $num . ' cerró el ' . SDateFormatUtils::formatDate($dt_cut, 'ddd D-m-Y') . ' , no se puede modificar.';
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