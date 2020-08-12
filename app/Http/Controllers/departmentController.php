<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\department;
use App\Models\area;
use App\Models\DepartmentRH;

class departmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = department::where('is_delete','0')->orderBy('name')->get();
        $datas->each(function($datas){
            $datas->area;
            $datas->rh;
        });
        return view('department.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $area = area::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $deptrhs = DepartmentRH::where('is_delete',0)->pluck('id','name');
        return view('department.create')->with('areas',$area)->with('deptrhs',$deptrhs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $department = department::create($request->all());
        $department->rh_department_id = $request->rh_department_id;
        $department->updated_by = session()->get('user_id');
        $department->created_by = session()->get('user_id');
        $department->save();
        return redirect('department')->with('mensaje','Departamento fue creado con exito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $area = area::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $data = department::findOrFail($id);
        $deptrhs = DepartmentRH::where('is_delete',0)->pluck('id','name');
        return view('department.edit', compact('data'))->with('areas',$area)->with('deptrhs',$deptrhs);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $department = department::findOrFail($id);
        $department->updated_by = session()->get('user_id');
        $department->update($request->all());
        return redirect('department')->with('mensaje', 'Departamento actualizado con exito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        if ($request->ajax()) {
            $department = department::find($id);
            $department->fill($request->all());
            $department->is_delete = 1;
            $department->updated_by = session()->get('user_id');
            $department->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function updateDepts(Request $request)
    {
        $lDepartments = json_decode($request->departments);
        // $deptIds = array();
        foreach ($lDepartments as $dept) {
            department::where('id', $dept->id)
                        ->update(['dept_group_id' => $dept->dept_group_id]);
            // $deptIds[] = $dept->id;
        }

        $lResp = department::where('is_delete', false)
                    ->select('id', 'name', 'dept_group_id')
                    ->get();

        return json_encode($lResp);
    }
}
