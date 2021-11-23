<?php namespace App\SUtils;

use Carbon\Carbon;
use App\Models\employees;

class SPrepayrollAdjustUtils {

    /**
     * Devuelve los ajustes autorizados para el renglón
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $employeId
     * @param string $adjType
     * 
     * @return array
     */
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

        $config = \App\SUtils\SConfiguration::getConfigurations();

        if ($config->enabledAdjAuths) {
            $lAuthAdjusts = [];
            foreach ($lAdjusts as $adj) {
                if (SPrepayrollAdjustUtils::isAdjustAuthorized($adj->id)) {
                    $lAuthAdjusts[] = $adj;
                }
            }

            return $lAuthAdjusts;
        }
        
        return $lAdjusts;
    }

    /**
     * Devuelve los ajustes autorizados para el caso
     *
     * @param string $date
     * @param string $time
     * @param int $applyTo
     * @param int $type
     * @param int $idEmployee
     * 
     * @return array
     */
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

        $config = \App\SUtils\SConfiguration::getConfigurations();

        if ($config->enabledAdjAuths) {
            $lAuthAdjusts = [];
            foreach ($lAdjusts as $adj) {
                if (SPrepayrollAdjustUtils::isAdjustAuthorized($adj->id)) {
                    $lAuthAdjusts[] = $adj;
                }
            }

            return $lAuthAdjusts;
        }

        return $lAdjusts;
    }

    /**
     * Undocumented function
     *
     * @param [type] $idEmployee
     * @param [type] $date
     * 
     * @return void
     */
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
        $periodProcessed = \DB::table('period_processed AS pp');

        if ($payTypeId == \SCons::PAY_W_Q) {
            $periodProcessed = $periodProcessed->join('hrs_prepay_cut AS hpc','hpc.id','=','pp.num_biweekly');
        }
        else {
            $periodProcessed = $periodProcessed->join('week_cut AS wc','wc.id', '=', 'pp.num_week');
        }

        $periodProcessed->where('wc.year', $year)
                        ->where('num', $number)
                        ->delete();

        /**
         * Eliminación de los datos procesados
         */
        $processedData = \DB::table('processed_data AS pd');

        if ($payTypeId == \SCons::PAY_W_Q) {
            $processedData = $processedData->join('hrs_prepay_cut AS hpc', 'hpc.id', '=', 'pd.biweek');
        }
        else {
            $processedData = $processedData->join('week_cut AS wc', 'wc.id', '=', 'pd.week');;
        }

        $processedData->where('wc.year', $year)
                        ->where('num', $number)
                        ->delete();
    }

    /**
     * Undocumented function
     *
     * @param [type] $adjsArray
     * @param [type] $type
     * @return boolean
     */
    public static function hasTheAdjustType($adjsArray, $type)
    {
        foreach ($adjsArray as $adj) {
            if ($type == $adj->adjust_type_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina si un ajuste está autorizado totalmente o no.
     * 
     * Consulta las autorizaciones que debe tener el ajuste y si todas están autorizadas retorna true,
     * si al menos una no está autorizada retorna false
     *
     * @param int $idAdjust
     * @return boolean
     */
    public static function isAdjustAuthorized($idAdjust)
    {
        $lAuths = \DB::table('prepayroll_auth_controls AS pac')
                        ->where('pac.prepayroll_adjust_id', $idAdjust)
                        ->where('pac.is_delete', 0)
                        ->get();

        foreach ($lAuths as $auth) {
            if (! $auth->is_authorized) {
                return false;
            }
        }

        return true;
    }
}

?>