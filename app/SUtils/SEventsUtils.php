<?php namespace App\SUtils;

use Carbon\Carbon;
use App\Models\holidayassign;
use App\Models\employees;

class SEventsUtils {
    public static function getAllEvents($date, $idEmployee = 0, $dept = 0, $group = 0, $area = 0, $idHolAss = 0)
    {
        $events = [];

        $employees = [];
        $ha = holidayassign::where('date', $date)
                            ->where('is_delete', false);
        if ($idHolAss > 0) {
            $ha = $ha->where('id', '!=', $idHolAss);
        }

        if ($idEmployee == 0 || $idEmployee == null) {
            if ($dept > 0) {
                $employees = employees::where('is_delete','0')
                                    ->where('is_active', true)
                                    ->where('department_id', $dept)
                                    ->orderBy('name', 'ASC')
                                    ->pluck('id');

                $ha = $ha->where('department_id', $dept)
                                ->get();
            }
            else if ($group > 0) {
                    $ha = $ha->where('group_assign_id', $group)
                                    ->get();
                }
                else if ($area > 0) {
                        $employees = DB::table('departments AS d')
                                        ->join('employees AS e','d.id','=','e.department_id')
                                        ->where('d.area_id', $area)
                                        ->pluck('e.id');

                        $ha = $ha->where('area_id', $area)
                                    ->get();
                    }
                    else {
                        return [];
                    }
        }
        else {
            $ha = $ha->where('employee_id', $idEmployee)
                                ->get();

            $employees[] = $idEmployee;
        }

        if (count($ha) > 0) {
            foreach ($ha as $h) {
                $events[] = $h;
            }
        }

        if (count($employees) == 0) {
            return $events;
        }

        $incidents = SEventsUtils::getIncidentsByDateEmployees($date, $employees);

        if (count($incidents) > 0) {
            foreach ($incidents as $incident) {
                $events[] = $incident;
            }
        }

        return $events;
    }

    public static function getIncidentsByDateEmployees($date = "", $employees = [])
    {
        $lIncidents = \DB::table('incidents AS i')
                        ->join('type_incidents AS ti', 'i.type_incidents_id', '=', 'ti.id')
                        ->join('incidents_day AS inday', 'i.id', '=', 'inday.incidents_id')
                        ->leftJoin('incident_ext_sys_links AS iext', 'i.id', '=', 'iext.incident_id')
                        ->whereIn('employee_id', $employees)
                        ->whereRaw("'".$date."' = inday.date")
                        ->select('iext.external_key', 'i.is_external', 'i.nts', 'ti.name AS type_name', 'inday.date', 'inday.num_day')
                        ->where('i.is_delete', false)
                        ->orderBy('i.id', 'ASC')
                        ->get();

        return $lIncidents;
    }
}

?>