<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\employees;
use App\Models\job;
use App\Models\way_register;
use App\Models\benefitsPolice;
use DB;

class employeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = employees::where('is_delete','0')->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->job;
            $datas->way_register;
        });
        return view('employee.index', compact('datas'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $job = job::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        
        return view('employee.create')->with('way',$way)
                                        ->with('job',$job)
                                        ->with('benPols',$benPols);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        employees::create($request->all());
        return redirect('employee')->with('mensaje','Empleado fue creado con exito');
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
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $job = job::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        $data = employees::findOrFail($id);

        return view('employee.edit', compact('data'))
                                        ->with('way',$way)
                                        ->with('job',$job)
                                        ->with('benPols',$benPols);
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
        employees::findOrFail($id)->update($request->all());
        return redirect('employee')->with('mensaje', 'Empleado actualizado con exito');
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
            $employee = employees::find($id);
            $employee->fill($request->all());
            $employee->is_delete = 1;
            $employee->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }

    public function supervisorsView($id = 1){

        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('departments.dept_group_id',$id)
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        return view('employee.supervisorsView', compact('employees'));
    }

    public function editShortname ($id) {
        $data = employees::findOrFail($id);
        return view('employee.editShortname')->with('data',$data);    
    }

    public function updateShortname (Request $request, $id){
        $employee = employees::findOrFail($id);
        $employee->short_name = $request->short_name;
        $employee->update();
        return redirect('employee/supervisorsView')->with('mensaje', 'Empleado actualizado con exito');    
    }
}
