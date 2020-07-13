<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentRH;

class DeptsRhController extends Controller
{
    public function saveRhDeptsFromJSON($lSiieRhDepts)
    {
        $lCapRhDepts = DepartmentRH::select('id', 'external_id')
                                    ->pluck('id', 'external_id');

        foreach ($lSiieRhDepts as $jRhDept) {
            try {
                $id = $lCapRhDepts[$jRhDept->id_department];
                $this->updRhDept($jRhDept, $id);
            }
            catch (\Throwable $th) {
                $this->insertRhDept($jRhDept);
            }
        }
    }
    
    private function updRhDept($jRhDept, $id)
    {
        DepartmentRH::where('id', $id)
                    ->update(
                            [
                            'name' => $jRhDept->dept_name,
                            'code' => $jRhDept->dept_code,
                            'is_delete' => $jRhDept->is_deleted,
                            'updated_by' => session()->get('user_id')
                            ]
                        );
    }
    
    private function insertRhDept($jRhDept)
    {
        $RhDept = new DepartmentRH();

        $RhDept->name = $jRhDept->dept_name;
        $RhDept->code = $jRhDept->dept_code;
        $RhDept->external_id = ($jRhDept->id_department);
        $RhDept->is_delete = $jRhDept->is_deleted;
        $RhDept->created_by = session()->get('user_id');
        $RhDept->updated_by = session()->get('user_id');

        $RhDept->save();
    }
}
