<?php namespace App\SData;

use Carbon\Carbon;
use App\Models\firstDayYear;
use App\Models\week_cut;

class SCutoffDates
{
    public static function processCutoffDates($sDate)
    {
        $oDate = Carbon::parse($sDate);
        $oFdY = firstDayYear::where('year', $oDate->year)
                            ->where('is_delete', 0)
                            ->first();

        if ($oFdY == null) {
            return false;
        }

        $oDate = Carbon::parse($oFdY->dt_date);
        $oDateEnd = clone $oDate;
        $oDateEnd->addWeek()->subDay();

        $lWeekCuts = [];
        $nextYear = $oDateEnd->year + 1;
        $currentYear = $oDateEnd->year;
        $num = 1;
        while ($oDateEnd->year < $nextYear) {
            $cut = new \stdClass();
            $cut->dt_ini = $oDate->toDateString();
            $cut->dt_end = $oDateEnd->toDateString();
            $cut->num = $num;
            $lWeekCuts[] = $cut;
            $oDate->addWeek();
            $oDateEnd->addWeek();
            $num++;
        }

        $lBdCuts = week_cut::where('year', $currentYear)
                            ->get();

        foreach ($lWeekCuts as $cut) {
            $dbCut = $lBdCuts->filter(function($item) use ($cut) {
                                    return $item->num == $cut->num;
                                })->first();
            
            if ($dbCut == null) {
                $dbCut = new week_cut();
                $dbCut->year = $currentYear;
                $dbCut->num = $cut->num;
                $dbCut->ini = $cut->dt_ini;
                $dbCut->fin = $cut->dt_end;
                $dbCut->created_by = session()->get('user_id');
                $dbCut->updated_by = session()->get('user_id');
                $dbCut->save();
            }
            else if ($dbCut->ini != $cut->dt_ini || $dbCut->fin != $cut->dt_end) {
                $dbCut->ini = $cut->dt_ini;
                $dbCut->fin = $cut->dt_end;
                $dbCut->updated_by = session()->get('user_id');
                $dbCut->save();
            }
        }

        return $lWeekCuts;
    }
}