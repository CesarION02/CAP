<?php namespace App\SUtils;

use Carbon\Carbon;

class SGenUtils {

    /**
     * Undocumented function
     *
     * @param integer $type
     * @param array $keys
     * @return void
     */
    public static function toEmployeeIds($payWay, $type = 0, $keys = [], $aEmployees = [])
    {
        $employees = \DB::table('employees AS e')
                            ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                            ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                            ->select('e.id', 'd.id AS dept_id', 'e.num_employee', 
                                        'e.name', 'e.is_overtime', 'e.ben_pol_id', 'external_id')
                            ->where('d.is_delete', false)
                            // ->where('e.id', 51)
                            ->where('e.is_active', true);
                            
        if (sizeof($aEmployees) > 0) {
            $employees = $employees->whereIn('e.id', $aEmployees);
        }
                            
        switch ($payWay) {
            case \SCons::PAY_W_Q:
                $employees = $employees->where('e.way_pay_id', \SCons::PAY_W_Q);
                break;
            case \SCons::PAY_W_S:
                $employees = $employees->where('e.way_pay_id', \SCons::PAY_W_S);
                break;
            
            default:
                # code...
                break;
        }

        switch ($type) {
            case 1:
                $employees = $employees->leftJoin('areas AS a', 'd.area_id', '=', 'a.id')
                                        ->where('a.is_delete', false)
                                        ->whereIn('a.id', $keys);
                break;
            case 2:
                $employees = $employees->whereIn('d.id', $keys);
                break;
            
            default:
                # code...
                break;
        }

        $employees = $employees->get();

        return $employees;
    }
}

?>