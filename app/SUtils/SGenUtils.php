<?php namespace App\SUtils;

class SGenUtils {

    /**
     * Regresa un array con los id de los empleados que cumplan con las condiciones recibidas
     *
     * @param integer $type
     * @param array $keys
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function toEmployeeIds($payWay, $type = 0, $keys = [], $aEmployees = [], $nocheca = 0)
    {
        $employees = \DB::table('employees AS e')
                            ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                            ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                            ->select('e.id', 'd.id AS dept_id', 'e.num_employee', 'e.way_pay_id', 'd.name AS dept_name',
                                        // 'e.name', 'e.is_overtime', 'e.ben_pol_id', 'external_id')
                                        'e.name', 'e.policy_extratime_id', 'e.ben_pol_id', 'e.external_id', 'd.area_id AS employee_area_id')
                            ->where('e.is_delete', false)
                            // ->where('e.id', 67)
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

        switch ($nocheca) {
            case 1:
                $employees = $employees->where('e.ben_pol_id','!=',2);
                break;
            
            default:
                break;
        }

        $employees = $employees->orderBy('e.name', 'ASC')->get();

        return $employees;
    }

    public static function toEmployeeQuery($payWay, $type = 0, $keys = [], $aEmployees = [], $nocheca = 0)
    {
        $employees = \DB::table('employees AS e')
                            ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                            ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                            ->select('e.id', 'd.id AS dept_id', 'e.num_employee', 'e.way_pay_id',
                                        // 'e.name', 'e.is_overtime', 'e.ben_pol_id', 'external_id')
                                        'e.name', 'e.policy_extratime_id', 'e.ben_pol_id', 'e.external_id')
                            ->where('e.is_delete', false)
                            // ->where('e.id', 67)
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

        switch ($nocheca) {
            case 1:
                $employees = $employees->where('e.ben_pol_id','!=',2);
                break;
            
            default:
                break;
        }

        return $employees;
    }
}

?>