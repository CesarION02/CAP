<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\department;
use App\Models\employees;
use App\Models\area;
use App\Models\way_pay;
use App\Models\DepartmentRH;
use App\Models\departmentsGroup;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SInfoWithPolicy;
use App\SUtils\SGenUtils;
use App\SData\SDataProcess;
use DB;

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
                                    ->orderBy('e.name')
                                    ->orderBy('date')
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
                                    ->orderBy('e.name')
                                    ->orderBy('date')
                                    ->orderBy('time')
                                    ->orderBy('dg.id');
                break;
            case 3:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->whereIn('d.id', $values)
                                    ->select('e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id','d.name AS depname')
                                    ->groupBy('date','type_id','e.name','e.num_employee')
                                    ->orderBy('e.name')
                                    ->orderBy('date')
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
                                    ->whereIn('a.id', $values)
                                    ->groupBy('e.name','date','type_id','e.num_employee','a.name')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 2:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->join('department_group AS dg', 'dg.id', '=', 'd.dept_group_id')
                                    ->whereIn('dg.id', $values)
                                    ->groupBy('e.name','date','type_id','e.num_employee','a.name')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 3:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->whereIn('d.id', $values)
                                    ->groupBy('e.name','date','type_id','e.num_employee','a.name')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 4:
                $register = $register->whereIn('e.id', $values)
                                    ->groupBy('e.name','date','type_id','e.num_employee')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
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

    public function genDelayReport()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        
        return view('report.reportsGen')
                    ->with('tReport', \SCons::REP_DELAY)
                    ->with('sTitle', 'Reporte de Retardos')
                    ->with('sRoute', 'reporteRetardos')
                    ->with('startOfWeek', $config->startOfWeek);
    }

    public function genHrExReport()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $lEmployees = SGenUtils::toEmployeeIds(0, 0, []);

        return view('report.reportsGen')
                    ->with('tReport', \SCons::REP_HR_EX)
                    ->with('sTitle', 'Reporte de Retardos y Percepciones Variables')
                    ->with('sRoute', 'reportepercepvariables')
                    ->with('lEmployees', $lEmployees)
                    ->with('startOfWeek', $config->startOfWeek);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function delaysReport(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;

        /**
         * 1: quincena
         * 2: semana
         * 3: todos
         */
        $payWay = $request->pay_way;
        $filterType = $request->i_filter;
        $ids = $request->elems;

        $lEmployees = SGenUtils::toEmployeeIds($payWay, $filterType, $ids);

        $lRows = SDelayReportUtils::processReport($sStartDate, $sEndDate, $payWay, \SCons::REP_DELAY, $lEmployees);

        $sPayWay = "";
        switch ($payWay) {
            case \SCons::PAY_W_Q :
                $sPayWay = "Quincena";
                break;
            case \SCons::PAY_W_S :
                $sPayWay = "Semana";
                break;
            default:
                $sPayWay = "Todos";
                break;
        }

        return view('report.reportDelaysView')
                    ->with('tReport', \SCons::REP_DELAY)
                    ->with('sStartDate', $sStartDate)
                    ->with('sEndDate', $sEndDate)
                    ->with('sPayWay', $sPayWay)
                    ->with('sTitle', 'Reporte de Retardos')
                    ->with('lRows', $lRows);
    }
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function hrExtReport(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;
        $iEmployee = $request->emp_id;

        if ($iEmployee > 0) {
            $lEmployees = SGenUtils::toEmployeeIds(0, 0, 0, [$iEmployee]);
            $payWay = $lEmployees[0]->way_pay_id;
        }
        else {
            /**
             * 1: quincena
             * 2: semana
             * 3: todos
             */
            $payWay = $request->pay_way;

            $filterType = $request->i_filter;
            $ids = $request->elems;
            $lEmployees = SGenUtils::toEmployeeIds($payWay, $filterType, $ids);
        }

        $lRows = SDataProcess::process($sStartDate, $sEndDate, $payWay, $lEmployees);

        $sPayWay = "";
        switch ($payWay) {
            case \SCons::PAY_W_Q :
                $sPayWay = "Quincena";
                break;
            case \SCons::PAY_W_S :
                $sPayWay = "Semana";
                break;
            default:
                $sPayWay = "Todos";
                break;
        }

        return view('report.reportDelaysView')
                    ->with('tReport', \SCons::REP_HR_EX)
                    ->with('sStartDate', $sStartDate)
                    ->with('sEndDate', $sEndDate)
                    ->with('sPayWay', $sPayWay)
                    ->with('sTitle', 'Reporte de Retardos y Percepciones Variables')
                    ->with('lRows', $lRows);
    }

    public function hrReport(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;
        $lEmployees = [];
        /**
         * 1: quincena
         * 2: semana
         * 3: todos
         */
        $tipoDatos = $request->tipodato;
        $payWay = $request->way_pay;
        $id = $request->vals;
        switch($request->reportType){
            case 1:
                
                $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->join('areas','areas.id','=','departments.area_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.way_pay_id',$payWay);
                for($i = 0 ; count($id) > $i ; $i++ ){
                   if($i != 0){
                        $employees = $employees->OrWhere('areas.id',$id[$i]);
                   }else{
                        $employees = $employees->where('areas.id',$id[$i]);
                   }
                }
                $employees = $employees->select('employees.id')->get();
                for($i = 0 ; count($employees) > $i ; $i++){
                    $lEmployees[$i] = $employees[$i]->id; 
                }
            break;
            case 2:
                $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.way_pay_id',$payWay);
                for($i = 0 ; count($id) > $i ; $i++ ){
                   if($i != 0){
                        $employees = $employees->OrWhere('departments.dept_group_id',$id[$i]);
                   }else{
                        $employees = $employees->where('departments.dept_group_id',$id[$i]);
                   }
                }
                $employees = $employees->select('employees.id')->get();
                for($i = 0 ; count($employees) > $i ; $i++){
                    $lEmployees[$i] = $employees[$i]->id; 
                }
            break;
            case 3:
                $employees = DB::table('employees')
                        ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.way_pay_id',$payWay);
                for($i = 0 ; count($id) > $i ; $i++ ){
                   if($i != 0){
                        $employees = $employees->OrWhere('dept_rh.id',$id[$i]);
                   }else{
                        $employees = $employees->where('dept_rh.id',$id[$i]);
                   }
                }
                $employees = $employees->select('employees.id AS id')->get();
                for($i = 0 ; count($employees) > $i ; $i++){
                    $lEmployees[$i] = $employees[$i]->id; 
                }
            break;
            case 4:
                $lEmployees = $request->vals;
            break;
        }
        //$lEmployees[0] = 32; 
        $lRows = SInfoWithPolicy::processInfo($sStartDate, $sEndDate, $payWay, $lEmployees,$tipoDatos);

        return view('report.reportView')
                    ->with('sTitle', 'Reporte de Checadas')
                    ->with('lRows', $lRows);
    } 

    public function prueba(){
        $start = '2020-05-01';
        $end = '2020-05-15';
        $way = 2;
        $year = '2020';    
        $employees[0] = 67;
        $key[0] = 2;

        //$employees[0] = 24;

        //$prueba = SInfoWithPolicy::standardization($start,$end,$way,2,$key,$employees);
        $prueba = SInfoWithPolicy::preProcessInfo($start,$year,$end,$way);
    }

    public function datosReporteSecretaria($reportType,$tipoDatos){
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
                $lDepts = DepartmentRH::select('id','name')->where('is_delete', false)->get();
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
        //$lWayPay = way_pay::select('id','name')->where('is_delete',false)->get();
        return view('report.datosReportView')->with('lAreas', $lAreas)
                                            ->with('lDepsGroups', $lDepsGroups)
                                            ->with('lDepts', $lDepts)
                                            ->with('lEmployees', $lEmployees)
                                            //->with('lWay',$lWayPay)
                                            ->with('reportType', $reportType)
                                            ->with('tipoDatos', $tipoDatos);
    }

    public function generarReporteSecretaria(Request $request){

    }
}
