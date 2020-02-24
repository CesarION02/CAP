<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\department;
use App\Models\employees;
use App\Models\register;
use App\Models\area;
use App\Models\departmentsGroup;
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

    /**
     * Recibe el tipo de reporte y en base a este retorna una colección con los valores posibles
     *
     * @param integer $reportType
     *                  1  Reporte por área
     *                  2  Reporte por grupo de departamentos
     *                  3  Reporte por departamentos
     *                  4  Reporte por empleados
     * 
     * @return view('report.reportES')
     */
    public function esReport($type = 0){
        $lAreas = null;
        $lDepsGroups = null;
        $lDepts = null;
        $lEmployees = null;

        switch ($type) {
            case 1:
                $lAreas = area::select('id','name')->where('is_delete', false)->get();
                break;
            case 2:
                $lDepsGroups = departmentsGroup::select('id','name')->where('is_delete', false)->get();
                break;
            case 3:
                $lDepts = department::select('id','name')->where('is_delete', false)->get();
                break;
            case 4:
                $lEmployees = employees::select('id', 'name', 'num_employee')->where('is_delete', false)->get();
                break;
            
            default:
                # code...
                break;
        }

        return view('report.reportES')->with('lAreas', $lAreas)
                                        ->with('lDepsGroups', $lDepsGroups)
                                        ->with('lDepts', $lDepts)
                                        ->with('lEmployees', $lEmployees)
                                        ->with('reportType', $type);

    }

    public function reporteESView(Request $request){
        $reportType = $request->reportType;
        $values = $request->vals;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        DB::enableQueryLog();

        $register = DB::table('registers AS r')
                    ->join('employees AS e', 'e.id', '=', 'r.employee_id');

        switch ($reportType) {
            case 1:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->join('areas AS a', 'a.id', '=', 'd.area_id')
                                    ->whereIn('a.id', $values)
                                    ->select('e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id','a.name AS areaname')
                                    ->groupBy('e.name','date','type_id','e.num_employee','a.name')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time')
                                    ->orderBy('a.id');
                break;
            case 2:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->join('department_group AS dg', 'dg.id', '=', 'd.dept_group_id')
                                    ->whereIn('dg.id', $values)
                                    ->select('e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id','dg.name AS groupname')
                                    ->groupBy('date','type_id','e.name','e.num_employee')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time')
                                    ->orderBy('dg.id');
                break;
            case 3:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->whereIn('d.id', $values)
                                    ->select('e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id','d.name AS depname')
                                    ->groupBy('date','type_id','e.name','e.num_employee')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time')
                                    ->orderBy('d.id');;
                break;
            case 4:
                $register = $register->whereIn('e.id', $values)
                                    ->select('e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id')
                                    ->groupBy('date','type_id','e.name','e.num_employee')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            
            default:
                # code...
                break;
        }

        $register = $register->whereBetween('r.date', [$startDate, $endDate])
                             ->get();

        return view('report.reporteESView')
                        ->with('reportType', $reportType)
                        ->with('lRegistries', $register);
    

    }

    /**
     * Recibe el tipo de reporte y en base a este retorna una colección con los valores posibles
     *
     * @param integer $reportType
     *                  1  Reporte por área
     *                  2  Reporte por grupo de departamentos
     *                  3  Reporte por departamentos
     *                  4  Reporte por empleados
     * 
     * @return view('report.reportRegs')
     */
    public function registriesReport($reportType = 0)
    {
        $lAreas = null;
        $lDepsGroups = null;
        $lDepts = null;
        $lEmployees = null;

        switch ($reportType) {
            case 1:
                $lAreas = area::select('id','name')->where('is_delete', false)->get();
                break;
            case 2:
                $lDepsGroups = departmentsGroup::select('id','name')->where('is_delete', false)->get();
                break;
            case 3:
                $lDepts = department::select('id','name')->where('is_delete', false)->get();
                break;
            case 4:
                $lEmployees = employees::select('id', 'name', 'num_employee')
                                        ->where('is_delete', false)
                                        ->orderBy('name', 'ASC')
                                        ->get();
                break;
            
            default:
                # code...
                break;
        }

        return view('report.reportRegs')->with('lAreas', $lAreas)
                                        ->with('lDepsGroups', $lDepsGroups)
                                        ->with('lDepts', $lDepts)
                                        ->with('lEmployees', $lEmployees)
                                        ->with('reportType', $reportType);
    }

    /**
     * Recibe el formulario con los datos a consultar y el tipo de reporte
     *
     * @param Request $request deberá contener el tipo de reporte, un arreglo con los valores a consultar, 
     *                  la fecha de inicio y la fecha final para consultar el rango de fechas de los registros (checadas)
     * @return view report.reportRegsView
     */
    public function reporteRegistrosView(Request $request)
    {
        $reportType = $request->reportType;
        $values = $request->vals;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        DB::enableQueryLog();

        $register = DB::table('registers AS r')
                    ->join('employees AS e', 'e.id', '=', 'r.employee_id');

        switch ($reportType) {
            case 1:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->join('areas AS a', 'a.id', '=', 'd.area_id')
                                    ->whereIn('a.id', $values);
                break;
            case 2:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->join('department_group AS dg', 'dg.id', '=', 'd.dept_group_id')
                                    ->whereIn('dg.id', $values);
                break;
            case 3:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->whereIn('d.id', $values);
                break;
            case 4:
                $register = $register->whereIn('e.id', $values);
                break;
            
            default:
                # code...
                break;
        }

        $register = $register->select('e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id')
                                ->whereBetween('r.date', [$startDate, $endDate])
                                ->get();

        return view('report.reportRegsView')
                        ->with('reportType', $reportType)
                        ->with('lRegistries', $register);
    }
}
