<?php namespace App\SUtils;

use Carbon\Carbon;
use App\Models\employees;

class SPrepayrollAdjustUtils {

    public static function getAdjustsOfRow($startDate, $endDate, $employeId, $adjType = "")
    {
        $lAdjusts = \DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.comments',
                                    'pa.adjust_type_id',
                                    'pa.apply_to',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id'
                                    )
                        ->whereBetween('dt_date', [$startDate, $endDate])
                        ->where('is_delete', false)
                        ->where('pa.employee_id', $employeId);

        if ($adjType != "") {
            if (is_array($adjType)) {
                $lAdjusts = $lAdjusts->whereIn('pa.adjust_type_id', $adjType);
            }
            else {
                $lAdjusts = $lAdjusts->where('pa.adjust_type_id', $adjType);
            }
        }

        $lAdjusts = $lAdjusts->get();

        return $lAdjusts;
    }

    public static function getAdjustForCase($date, $time, $applyTo, $type, $idEmployee)
    {
        $lAdjusts = \DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.adjust_type_id',
                                    'pa.apply_to',
                                    'pa.comments',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id'
                                    )
                        ->where('dt_date', $date)
                        ->where('dt_time', $time)
                        ->where('is_delete', false)
                        ->where('pa.apply_to', $applyTo)
                        ->where('pa.adjust_type_id', $type)
                        ->where('pa.employee_id', $idEmployee)
                        ->get();

        return $lAdjusts;
    }

    public static function verifyProcessedData($idEmployee, $date)
    {
        $emp = employees::find($idEmployee);
        $payTypeId = $emp->way_pay_id;
        $oDate = Carbon::parse($date);
        $year = $oDate->year;

        $oNumber = null;
        if ($payTypeId == \SCons::PAY_W_Q) {
            $quin = \DB::table('hrs_prepay_cut AS hpc')
                        ->where('dt_cut', '>=', $date)
                        ->where('year', $year)
                        ->where('is_delete', false)
                        ->orderBy('dt_cut', 'ASC')
                        ->take(1)
                        ->get();

            $oNumber = $quin[0];
        }
        else {
            $week = \DB::table('week_cut AS wc')
                        ->whereRaw("'" . $date . "' BETWEEN ini AND fin")
                        ->where('year', $year)
                        ->get();

            $oNumber = $week[0];
        }

        $number = $oNumber->num;

        /**
         * Eliminación de control de datos procesados
         */
        $periodProcessed = \DB::table('period_processed');
        if ($payTypeId == \SCons::PAY_W_Q) {
            $periodProcessed = $periodProcessed->where('num_biweekly', $number);
        }
        else {
            $periodProcessed = $periodProcessed->where('num_week', $number);
        }

        $periodProcessed->where('year', $year)
                        ->delete();

        /**
         * Eliminación de los datos procesados
         */
        $processedData = \DB::table('processed_data');
        if ($payTypeId == \SCons::PAY_W_Q) {
            $processedData = $processedData->where('biweek', $number);
        }
        else {
            $processedData = $processedData->where('week', $number);
        }

        $processedData->where('year', $year)
                        ->delete();
    }

    public static function hasTheAdjustType($adjsArray, $type)
    {
        foreach ($adjsArray as $adj) {
            if ($type == $adj->adjust_type_id) {
                return true;
            }
        }

        return false;
    }
}

?>