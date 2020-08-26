<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentRH;
use App\Models\department;

class DeptsRhController extends Controller
{
    public function index()
    {
        $datas = DepartmentRH::where('is_delete','0')->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->default_dept;
        });
        return view('departmentRH.index', compact('datas'));
    }

    public function edit($id)
    {
        $data = DepartmentRH::findOrFail($id);
        $departments = department::where('is_delete',0)->pluck('id','name');
        return view('departmentRH.edit', compact('data'))->with('departments',$departments);
    }

    public function update(Request $request, $id)
    {
        $department = departmentRH::findOrFail($id);
        $department->updated_by = session()->get('user_id');
        $department->default_dept_id = $request->department_id;
        $department->save();
        return redirect('departmentRH')->with('mensaje', 'Departamento RH actualizado con éxito');
    }
    
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
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $RhDept = new DepartmentRH();

        $RhDept->name = $jRhDept->dept_name;
        $RhDept->code = $jRhDept->dept_code;
        $RhDept->external_id = ($jRhDept->id_department);
        $RhDept->is_delete = $jRhDept->is_deleted;
        $RhDept->created_by = session()->get('user_id');
        $RhDept->updated_by = session()->get('user_id');
        
        $RhDept->default_dept_id = $config->dept_pre;

        $RhDept->save();
    }
}
