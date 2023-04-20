<?php namespace App\SUtils;

class SIncidentsUtils
{
    /**
     * Obtiene los ajustes relacionados a una incidencia
     * 
     * @param int $idIncident
     * @return \Illuminate\Support\Collection
     */
    public static function getIncidentAdjusts($idIncident)
    {
        $lAdjusts =  \DB::table('adjust_link AS al')
                    ->join('prepayroll_adjusts AS pa', 'pa.id', '=', 'al.adjust_id')
                    ->join('prepayroll_adjusts_types AS at', 'at.id', '=', 'pa.adjust_type_id')
                    ->select('pa.id', 'pa.dt_date', 'pa.dt_time', 'pa.comments', 'pa.adjust_type_id', 'at.type_name AS adjust_type_name', 'pa.employee_id')
                    ->where('al.incident_id', '=', $idIncident)
                    ->where('pa.is_delete', '=', '0')
                    ->orderBy('pa.updated_by', 'DESC')
                    ->get();

        return $lAdjusts;
    }
}