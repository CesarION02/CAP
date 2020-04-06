<?php

namespace App\Http\Controllers;

use App\Models\firstDayYear;

class fdyController extends Controller
{
    public function saveFDYFromJSON($lSiieFdy)
    {
        $lCapFDYs = firstDayYear::select('id', 'external_id')
                                    ->pluck('id', 'external_id');
        
        foreach ($lSiieFdy as $jFdy) {
            try {
                $id = $lCapFDYs[$jFdy->year];
                $this->updFDY($jFdy, $id);
            }
            catch (\Throwable $th) {
                $this->insertFdy($jFdy);
            }
        }
    }

    
    private function updFDY($jFdy, $id)
    {
        firstDayYear::where('id', $id)
                    ->update(
                            [
                            'dt_date' => $jFdy->dt_date,
                            'year' => $jFdy->year,
                            'is_delete' => $jFdy->is_deleted,
                            ]
                        );
    }

    
    private function insertFdy($jFdy)
    {
        $fdy = new firstDayYear();

        $fdy->year = $jFdy->year;
        $fdy->dt_date = $jFdy->dt_date;
        $fdy->external_id = $jFdy->year;
        $fdy->is_delete = $jFdy->is_deleted;

        $fdy->save();
    }
}
