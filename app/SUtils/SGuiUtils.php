<?php namespace App\SUtils;

use Carbon\Carbon;

class SGuiUtils {

    public static function getAreasAndDepts()
    {
        $areas = \DB::table('areas AS a')
                        ->select('id', 'name')
                        ->where('is_delete', false)
                        ->orderBy('name', 'ASC')
                        ->get();

        $depts = \DB::table('departments AS d')
                        ->select('id', 'name')
                        ->where('is_delete', false)
                        ->orderBy('name', 'ASC')
                        ->get();
                    

        return [$areas, $depts];
    }
}

?>