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
    public static function toEmployeeIds($type = 0, $keys = [])
    {
        if ($type == 0 || sizeof($keys) == 0) {
            return [];
        }

        $employees = \DB::table('employees AS e')
                            ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                            ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                            ->select('e.id')
                            ->where('d.is_delete', false)
                            ->where('e.is_active', true);

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