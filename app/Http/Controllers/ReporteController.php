<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\department;
use App\Models\employee;
use App\Models\register;
use DateTime;
use DB;
use PDF;

class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function reporteESplanta($type){
        if($type == 1){
            $departments = department::where('is_delete','0')->where('area_id','3')->orderBy('id','ASC')->pluck('id','name');
            return view('report.reportES', compact('departments'))->with('type',$type);

        }
        else{
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->select('employees.id AS idEmp','employees.name AS nameEmp')
                        ->where('employees.is_delete','0')
                        ->where('departments.area_id',3)
                        ->get();
            return view('report.reportES', compact('employees'))->with('type',$type);
        }

    }

    public function generarReporteES(Request $request){
        $register = DB::table('registers')
                    ->join('employees','employees.id','=','registers.employee_id')
                    ->select('employees.name AS name','date AS fecha','time AS tiempo','type_id AS tipo')
                    ->where('employees_id',$request->empleado)
                    ->whereBetween('date',[$request->fechaini, $request->fechafin])
                    ->get();
        
        $departments = department::where('is_delete','0')->where('area_id','3')->orderBy('id','ASC')->pluck('id','name');

    }
}
