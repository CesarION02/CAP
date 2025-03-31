<?php 
namespace App\SUtils;

use Carbon\Carbon;

class SCheckDaysVobo {
    public static function checkDays($employee, $today, $ini_date, $end_date = null) {
        try {
            $isInRange = true;
            $lDays = [];
            $message = '';
            $way_pay = $employee->way_pay_id;
    
            if (!$end_date) {
                $end_date = $ini_date;
            }
    
            $dt_cut = '';
            if ($way_pay == 2) {
                $oCut = \DB::table('week_cut')
                            ->where('ini', '>=', $ini_date)
                            ->where('fin', '<=', $ini_date)
                            ->first();
    
                if($oCut == null){
                    return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                }
                
                $dt_cut = $oCut->fin;
            } else if ($way_pay == 1) {
                $oCut = \DB::table('hrs_prepay_cut')
                            ->where('dt_cut', '>=',$ini_date)
                            ->where('is_delete', 0)
                            ->orderBy('dt_cut', 'asc')
                            ->first();
    
                if($oCut == null){
                    return json_encode(['isInRange' => true, 'days' => [], 'message' => '']);
                }
    
                $dt_cut = $oCut->dt_cut;
            }
    
            $config = \App\SUtils\SConfiguration::getConfigurations();
            $days = $config->daysToCloseVobo;
    
            $oDt_cut = Carbon::parse($dt_cut)->add('day', $days)->endOfDay();
            $oToday = Carbon::parse($today)->endOfDay();
    
            if ($oToday->gt($oDt_cut)) {
                $isInRange = false;
                $message = 'La prenomina esta cerrada, no se pueden enviar incidencias';
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