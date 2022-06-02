<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PrepayrollReportController;
use Illuminate\Http\Request;
use App\Models\department;
use App\Models\employees;
use App\Models\area;
use App\Models\prepayrollAdjType;
use App\Models\prepayrollAdjust;
use App\Models\DepartmentRH;
use App\Models\typeincident;
use App\Models\departmentsGroup;
use App\Models\PrepayrollDelegation;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SReportsUtils;
use App\SUtils\SInfoWithPolicy;
use App\SUtils\SHolidayWork;
use App\SUtils\SGenUtils;
use App\SUtils\SPermissions;
use App\SUtils\SPrepayrollUtils;
use App\SUtils\SDateUtils;
use App\SUtils\SPayrollDelegationUtils;
use App\SData\SDataProcess;
use Illuminate\Support\Collection;
use DB;
use Carbon\Carbon;
use App\SUtils\SReg;
use GuzzleHttp\Client as GuzzleClient;

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
            case 5:
                $numero = session()->get('name');
                $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
                $dgu = DB::table('group_dept_user')
                    ->where('user_id',$usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
                $Adgu = [];
                for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
                }
                //$lEmployees = employees::select('id', 'name', 'num_employee')->where('is_delete', false)->->get(); 
                $lEmployees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->orderBy('employees.name')
                        ->select('employees.name AS name','employees.num_employee AS num_employee','employees.id AS id')
                        ->get();
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
                                    ->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id','a.name AS areaname', 'e.external_id')
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
                                    ->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id','dg.name AS groupname', 'e.external_id')
                                    ->groupBy('e.name','date','type_id','e.num_employee')
                                    ->orderBy('e.name')
                                    ->orderBy('date')
                                    ->orderBy('time')
                                    ->orderBy('dg.id');
                break;
            case 3:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->whereIn('d.id', $values)
                                    ->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id','d.name AS depname', 'e.external_id')
                                    ->groupBy('e.name','date','type_id','e.num_employee')
                                    ->orderBy('e.name')
                                    ->orderBy('date')
                                    ->orderBy('time')
                                    ->orderBy('d.id');;
                break;
            case 4:
                $register = $register->whereIn('e.id', $values)
                                    ->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id', 'e.external_id')
                                    ->groupBy('e.name','date','type_id','e.num_employee')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 5:
                $register = $register->whereIn('e.id', $values)
                                    ->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id', 'e.external_id')
                                    ->groupBy('e.name','date','type_id','e.num_employee')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                
                $reportType = 4;
                break;
            
            default:
                # code...
                break;
        }
        

        $register = $register->whereBetween('r.date', [$startDate, $endDate])
                             ->where('r.is_delete',0)
                             ->get();

        foreach ($register as $reg) {
            if ($reg->type_id == 2) { // si la checada es de salida
                $reg = SReportsUtils::setAbsencesAndHolidays($reg->id, $reg->date, $reg);
            }
        }

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
            case 5:
                $numero = session()->get('name');
                $usuario = DB::table('users')
                        ->where('name',$numero)
                        ->get();
                $dgu = DB::table('group_dept_user')
                        ->where('user_id',$usuario[0]->id)
                        ->select('groupdept_id AS id')
                        ->get();
                $Adgu = [];
                for($i=0;count($dgu)>$i;$i++){
                    $Adgu[$i]=$dgu[$i]->id;
                }
                //$lEmployees = employees::select('id', 'name', 'num_employee')->where('is_delete', false)->->get(); 
                $lEmployees = DB::table('employees')
                            ->join('jobs','jobs.id','=','employees.job_id')
                            ->join('departments','departments.id','=','employees.department_id')
                            ->join('department_group','department_group.id','=','departments.dept_group_id')
                            ->orderBy('employees.job_id')
                            ->where('employees.is_delete','0')
                            ->where('employees.is_active','1')
                            ->whereIn('departments.dept_group_id',$Adgu)
                            ->orderBy('employees.name')
                            ->select('employees.name AS name','employees.num_employee AS num_employee','employees.id AS id')
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
        $checadasbrutas = $request->checadasbrutas;
        if($request->checadasbrutas != null){
            $checadasbrutas = 1;
        }else{
            $checadasbrutas = 0;
        }

        DB::enableQueryLog();

        $register = DB::table('registers AS r')
                    ->join('employees AS e', 'e.id', '=', 'r.employee_id');

        switch ($reportType) {
            case 1:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->join('areas AS a', 'a.id', '=', 'd.area_id')
                                    ->whereIn('a.id', $values)
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 2:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->join('department_group AS dg', 'dg.id', '=', 'd.dept_group_id')
                                    ->whereIn('dg.id', $values)
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 3:
                $register = $register->join('jobs AS j', 'j.id', '=', 'e.job_id')
                                    ->join('departments AS d', 'd.id', '=', 'j.department_id')
                                    ->whereIn('d.id', $values)
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 4:
                $register = $register->whereIn('e.id', $values)
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                break;
            case 5:
                $register = $register->whereIn('e.id', $values)
                                    ->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id')
                                    ->orderBy('date')
                                    ->orderBy('e.name')
                                    ->orderBy('time');
                $reportType = 4;
                break;
            default:
                # code...
                break;
        }
        if($checadasbrutas == 1){
               
        }else{
            $register = $register->groupBy('date','type_id','e.name','e.num_employee'); 
        }

        $register = $register->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id', 'e.external_id')
                                ->whereBetween('r.date', [$startDate, $endDate])
                                ->where('r.is_delete',0)
                                ->get();

        foreach ($register as $reg) {
            if ($reg->type_id == 2) { // si la checada es de salida
                $reg = SReportsUtils::setAbsencesAndHolidays($reg->id, $reg->date, $reg);
            }
        }

        return view('report.reportRegsView')
                        ->with('reportType', $reportType)
                        ->with('lRegistries', $register);
    }

    public function genDelayReport()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $lEmployees = SGenUtils::toEmployeeIds(0, 0, []);
        
        return view('report.reportsGen')
                    ->with('tReport', \SCons::REP_DELAY)
                    ->with('sTitle', 'Reporte de Retardos')
                    ->with('sRoute', 'reporteRetardos')
                    ->with('lEmployees', $lEmployees)
                    ->with('startOfWeek', $config->startOfWeek);
    }

    public function genHrExReport()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $bDirect = false;
        $payType = 0;
        $bDelegation = null;
        $subEmployees = SPrepayrollUtils::getEmployeesByUser(\Auth::user()->id, $bDirect, $payType, $bDelegation);
        if ($subEmployees == null) {
            $lEmployees = SGenUtils::toEmployeeIds(0, 0, []);
        }
        else {
            $qEmployees = SGenUtils::toEmployeeQuery(0, 0, []);

            $lEmployees = $qEmployees->whereIn('e.id', $subEmployees)
                            ->orderBy('e.name', 'ASC')
                            ->get();
        }

        return view('report.reportsGen')
                    ->with('tReport', \SCons::REP_HR_EX)
                    ->with('sTitle', 'Reporte de tiempos extra')
                    ->with('sRoute', 'reportetiemposextra')
                    ->with('lEmployees', $lEmployees)
                    ->with('startOfWeek', $config->startOfWeek);
    }

    public function genHrExReportDelegations()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $payType = 0;
        $bDirect = 0;
        $iDelegations = 0;
        $oPayrolls = SPayrollDelegationUtils::getDelegationsPayrolls(\Auth::user()->id);

        if (count($oPayrolls->weeks) == 0 && count($oPayrolls->biweeks) == 0) {
            return redirect()->back()->withErrors(['No hay semanas o quincenas delegadas para ti.']);
        }

        return view('report.reportsGenDelegation')
                    ->with('tReport', \SCons::REP_HR_EX)
                    ->with('sTitle', 'Reporte de tiempo extra delegado')
                    ->with('sRoute', 'reportetiemposextra')
                    // ->with('lEmployees', $lEmployees)
                    ->with('oPayrolls', $oPayrolls)
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
        $iEmployee = $request->emp_id;
        $nochecan = $request->nochecan;
        if($request->nochecan != null){
            $nochecan = 1;
        }else{
            $nochecan = 0;
        }
        $oStartDate = Carbon::parse($sStartDate);
        $oEndDate = Carbon::parse($sEndDate);

        if (! $oStartDate->lessThanOrEqualTo($oEndDate)) {
            return \Redirect::back()->withErrors(['Error', 'La fecha de inicio debe ser previa a la fecha final']);
        }

        if ($request->optradio == "employee") {
            if ($iEmployee > 0) {
                $lEmployees = SGenUtils::toEmployeeIds(0, 0, 0, [$iEmployee],$nochecan);
                $payWay = $lEmployees[0]->way_pay_id;
            }
            else {
                return \Redirect::back()->withErrors(['Error', 'Debe seleccionar empleado']);
            }
        }
        else {
            /**
             * 1: quincena
             * 2: semana
             * 3: todos
             */
            $payWay = $request->pay_way == null ? \SCons::PAY_W_S : $request->pay_way;

            $filterType = $request->i_filter;
            $ids = $request->elems;
            $lEmployees = SGenUtils::toEmployeeIds($payWay, $filterType, $ids, [] , $nochecan);
        }

        $lRows = SDataProcess::process($sStartDate, $sEndDate, $payWay, $lEmployees);

        $aEmployees = $lEmployees->pluck('num_employee', 'id');
        $lEmpWrkdDays = SDelayReportUtils::getTheoreticalDaysOffBasedOnDaysWorked($lRows, $aEmployees, $sStartDate, $sEndDate);

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

        $adjTypes = prepayrollAdjType::get()->toArray();

        $lAdjusts = DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.comments',
                                    'pa.apply_to',
                                    'pa.adjust_type_id',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id'
                                    )
                        ->whereBetween('dt_date', [$sStartDate, $sEndDate])
                        ->where('is_delete', false)
                        ->get();

        return view('report.reportRetardos')
                    ->with('tReport', \SCons::REP_HR_EX)
                    ->with('sStartDate', $sStartDate)
                    ->with('sEndDate', $sEndDate)
                    ->with('sPayWay', $sPayWay)
                    ->with('sTitle', 'Reporte de retardos')
                    ->with('adjTypes', $adjTypes)
                    ->with('lAdjusts', $lAdjusts)
                    ->with('lEmpWrkdDays', $lEmpWrkdDays)
                    ->with('lRows', $lRows);
    }

    function timesTotal($lRows, $lEmployees)
    {
        $delayTot;
        $extraHoursTot;
        $prematureOutTot;
        $absences;
        $sundays;
        $daysOff;
        foreach($lEmployees as $lEmployee){
            $delayTot = 0;
            $prematureOutTot = 0;
            $extraHoursTot = 0;
            $absences = 0;
            $sundays = 0;
            $daysOff = 0;
            foreach($lRows as $lRow){
                if($lEmployee->id === $lRow->idEmployee){
                    $delayTot = $delayTot + $lRow->entryDelayMinutes;
                    $extraHoursTot = $extraHoursTot + $lRow->overMinsTotal;
                    $prematureOutTot = $prematureOutTot + $lRow->prematureOut;
                    $sundays = $sundays + $lRow->isSunday;
                    $daysOff = $daysOff + $lRow->isDayOff;
                    if($lRow->hasAbsence){
                        $absences++;
                    }
                }
            }
            $lEmployee->entryDelayMinutes = $delayTot;
            $lEmployee->extraHours = $extraHoursTot;
            $lEmployee->prematureOut = $prematureOutTot;
            $lEmployee->isSunday = $sundays;
            $lEmployee->isDayOff = $daysOff;
            $lEmployee->hasAbsence = $absences;
        }

        return $lEmployees;
    }

    /**
     * Muestra reporte de tiempos extra
     *
     * @param Request $request
     * @return void
     */
    public function hrExtReport(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;
        $iEmployee = $request->emp_id;
        $reportMode = $request->report_mode;
        $bDelegation = $request->delegation;
        $iPayrollYear = $request->year;
        $iPayrollNumber = $request->payroll_number;
        $lComments = \DB::table('comments')->where('is_delete', 0)->get();

        $oStartDate = Carbon::parse($sStartDate);
        $oEndDate = Carbon::parse($sEndDate);

        if (! $oStartDate->lessThanOrEqualTo($oEndDate)) {
            return \Redirect::back()->withErrors(['Error', 'La fecha de inicio debe ser previa a la fecha final']);
        }

        if ($request->optradio == "employee") {
            if ($iEmployee > 0) {
                $lEmployees = SGenUtils::toEmployeeIds(0, 0, 0, [$iEmployee]);
                $payWay = $lEmployees[0]->way_pay_id;
            }
            else {
                return \Redirect::back()->withErrors(['Error', 'Debe seleccionar empleado']);
            }
        }
        else {
            /**
             * 1: quincena
             * 2: semana
             * 3: todos
             */
            $payWay = $request->pay_way == null ? \SCons::PAY_W_S : $request->pay_way;

            $filterType = $request->i_filter;
            $ids = $request->elems;
            $lEmployees = SGenUtils::toEmployeeIds($payWay, $filterType, $ids);
        }

        $iDelegation = null;
        if ($bDelegation) {
            $oDelegation = PrepayrollDelegation::where('number_prepayroll', $iPayrollNumber)
                                                ->where('year', $iPayrollYear)
                                                ->where('is_delete', false)
                                                ->where('pay_way_id', $payWay)
                                                ->where('user_delegated_id', \Auth::user()->id)
                                                ->first();
            if ($oDelegation == null) {
                $roles = \Auth::user()->roles;
                $config = \App\SUtils\SConfiguration::getConfigurations(); // Obtengo las configuraciones del sistema

                $seeAll = false;
                foreach ($roles as $rol) {
                    if (in_array($rol->id, $config->rolesCanSeeAll)) {
                        $seeAll = true;
                        break;
                    }
                }

                if (! $seeAll) {
                    return \Redirect::back()->withErrors(['Error', 'No tiene delegación para el número de prenómina seleccionado']);
                }
            }

            $dates = SDateUtils::getDatesOfPayrollNumber($iPayrollNumber, $iPayrollYear, $payWay);
            $sStartDate = $dates[0];
            $sEndDate = $dates[1];
            $oStartDate = Carbon::parse($sStartDate);
            $oEndDate = Carbon::parse($sEndDate);
            $iDelegation = $oDelegation->id_delegation;
        }
        
        $bDirect = false;
        $subEmployees = SPrepayrollUtils::getEmployeesByUser(\Auth::user()->id, $payWay, $bDirect, $iDelegation);
        if ($subEmployees != null && count($subEmployees) >= 0) {
            $lColEmps = collect($lEmployees);
    
            $lEmployees = $lColEmps->whereIn('id', $subEmployees);
        }

        $lRows = SDataProcess::process($sStartDate, $sEndDate, $payWay, $lEmployees);

        $aEmployees = $lEmployees->pluck('num_employee', 'id');
        $lEmpWrkdDays = SDelayReportUtils::getTheoreticalDaysOffBasedOnDaysWorked($lRows, $aEmployees, $sStartDate, $sEndDate);

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

        $adjTypes = prepayrollAdjType::get()->toArray();

        $lAdjusts = DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.comments',
                                    'pa.apply_to',
                                    'pa.adjust_type_id',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id'
                                    )
                        ->whereBetween('dt_date', [$sStartDate, $sEndDate])
                        ->where('is_delete', false)
                        ->get();

        $bModify = SPermissions::hasPermission(\Auth::user()->id, 'ajustes_rep_te');

        PrepayrollReportController::prepayrollReportVobos($sStartDate, $sEndDate);

        $lDeptJobs = DB::table('employees AS e')
                        ->join('departments AS d', 'e.department_id', '=', 'd.id')
                        ->join('jobs AS j', 'e.job_id', '=', 'j.id')
                        ->selectRaw('e.num_employee, CONCAT("DEPTO.: ", d.name, ", PUESTO: ", j.name) AS dept_job')
                        ->pluck('dept_job', 'num_employee');
        
        if ($reportMode == \SCons::REP_HR_EX) {
            /**
             * Obtención de vobos de empleados
             */
            $isPrepayrollInspection = false;
            $lEmpVobos = [];
            if (($payWay == \SCons::PAY_W_Q || $payWay == \SCons::PAY_W_S) && env('VOBO_BY_EMP_ENABLED')) {
                $number = SDateUtils::getNumberOfDate($sStartDate, $payWay);
                $dates = SDateUtils::getDatesOfPayrollNumber($number, $oStartDate->year, $payWay);
                
                if ($dates[0] == $sStartDate && $dates[1] == $sEndDate) {
                    $lEmpVobos = DB::table('prepayroll_report_emp_vobos AS evb')
                                        ->join('users AS u', 'evb.vobo_by_id', '=', 'u.id')
                                        ->join('employees AS e', 'evb.employee_id', '=', 'e.id')
                                        ->where('evb.is_delete', 0)
                                        ->where('year', $oStartDate->year)
                                        ->select('u.name AS user_name', 'evb.employee_id', 'evb.vobo_by_id', 'e.num_employee');

                    if ($payWay == \SCons::PAY_W_Q) {
                        $lEmpVobos = $lEmpVobos->where('evb.is_biweek', true)
                                                ->where('evb.num_biweek', $number);
                    }
                    else {
                        $lEmpVobos = $lEmpVobos->where('evb.is_week', true)
                                                ->where('evb.num_week', $number);
                    }

                    $lEmpVobos = $lEmpVobos->get()->keyBy('num_employee')->toArray();

                    $isPrepayrollInspection = true;
                }
            }

            return view('report.reportDelaysView')
                    ->with('tReport', \SCons::REP_HR_EX)
                    ->with('sStartDate', $sStartDate)
                    ->with('sEndDate', $sEndDate)
                    ->with('sPayWay', $sPayWay)
                    ->with('sTitle', 'Reporte de tiempos extra')
                    ->with('adjTypes', $adjTypes)
                    ->with('lAdjusts', $lAdjusts)
                    ->with('lEmpVobos', $lEmpVobos)
                    ->with('lDeptJobs', $lDeptJobs)
                    ->with('isPrepayrollInspection', $isPrepayrollInspection)
                    ->with('lEmpWrkdDays', $lEmpWrkdDays)
                    ->with('bModify', $bModify)
                    ->with('registriesRoute', route('registro_ajuste'))
                    ->with('lRows', $lRows)
                    ->with('lComments', $lComments);
        }
        else {
            $lEmployees = $this->timesTotal($lRows, $lEmployees);
            foreach($lEmployees as $emp){
                $oCom = $lAdjusts->where('employee_id',$emp->id)->all();
                $arr = [];
                foreach($oCom as $com){
                    array_push($arr, $com->dt_date.", ".$com->comments);
                }
                $emp->comments = $arr;
            }
            
            return view('report.reportDelaysTotView')
                    ->with('tReport', \SCons::REP_HR_EX_TOT)
                    ->with('sStartDate', $sStartDate)
                    ->with('sEndDate', $sEndDate)
                    ->with('sPayWay', $sPayWay)
                    ->with('sTitle', 'Reporte de tiempos extra')
                    ->with('adjTypes', $adjTypes)
                    ->with('lAdjusts', $lAdjusts)
                    ->with('lEmpWrkdDays', $lEmpWrkdDays)
                    ->with('bModify', $bModify)
                    ->with('registriesRoute', route('registro_ajuste'))
                    ->with('lRows', $lRows)
                    ->with('lEmployees', $lEmployees);
        }
        
    }

    public function hrReport(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;
        $year = Carbon::parse($sStartDate);
        $year = $year->format('Y');
        $lEmployees = [];
        /**
         * 1: quincena
         * 2: semana
         * 3: todos
         */
        $tipoDatos = $request->tipodato;
        $payWay = $request->way_pay;
        $id = 0;
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
                        ->where('employees.is_active', true)
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
                if($i == 0){
                    return redirect('report/datosreportestps/1/2')->with('mensaje','La periodicidad de pago tiene un error');
                }
            break;
            case 2:
                $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', true)
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
                if($i == 0){
                    return redirect('report/datosreportestps/2/2')->with('mensaje','La periodicidad de pago tiene un error');
                }
            break;
            case 3:
                $employees = DB::table('employees')
                        ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', true)
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
                if($i == 0){
                    return redirect('report/datosreportestps/3/2')->with('mensaje','La periodicidad de pago tiene un error');
                }
            break;
            case 4:
                $comprobacion = DB::table('employees')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', true)
                        ->where('employees.way_pay_id',$payWay);
                for($i = 0 ; count($id) > $i ; $i++ ){
                    if($i != 0){
                        $comprobacion = $comprobacion->OrWhere('employees.id',$id[$i]);
                    }else{
                        $comprobacion = $comprobacion->where('employees.id',$id[$i]);
                    }
                }
                $comprobacion = $comprobacion->get();
                if(count($comprobacion) == 0){
                    return redirect('report/datosreportestps/4/2')->with('mensaje','La periodicidad de pago tiene un error');    
                }
                $lEmployees = $id; 
            break;
            case 5:
                $employees = DB::table('employees')
                    ->orderBy('employees.job_id')
                    ->where('employees.is_delete','0')
                    ->where('employees.is_active', true)
                    ->where('employees.way_pay_id',$payWay)
                    ->get();

                    for($i = 0 ; count($employees) > $i ; $i++){
                        $lEmployees[$i] = $employees[$i]->id; 
                    }
                break;
            
        }
        $prueba = SInfoWithPolicy::preProcessInfo($sStartDate,$year,$sEndDate,$payWay,0);
        //SHolidayWork::holidayWorked($sStartDate,$sEndDate);
        
        //$lEmployees[0] = 32; 

        $config = \App\SUtils\SConfiguration::getConfigurations(); 
        $tipoContrato = $config->tp_rec;
        $Acontrato = [];
        for($i = 0 ; $i < count($tipoContrato) ; $i++){
            $Acontrato[$i] = $tipoContrato[$i]->id;
        }

        $lRows = DB::table('processed_data')
                        ->join('employees','employees.id','=','processed_data.employee_id')
                        ->whereIn('employees.id',$lEmployees)
                        //->whereIn('employees.tp_rec_id',$Acontrato)
                        ->where(function($query) use ($sStartDate,$sEndDate) {
                            $query->whereBetween('inDate',[$sStartDate,$sEndDate])
                            ->OrwhereBetween('outDate',[$sStartDate,$sEndDate]);
                        })
                        ->orderBy('employees.num_employee')
                        ->orderBy('week')
                        ->orderBy('biweek')
                        ->orderBy('outDate')
                        ->orderBy('week')
                        ->orderBy('biweek')
                        ->select(['employees.num_employee','employees.name', 'employees.external_id','processed_data.week','processed_data.biweek','processed_data.*'])
                        ->get();
        //$lEmployees = $id;
        $incapacidades = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('incidents_day','incidents_day.incidents_id','=','incidents.id')
                        ->where('employees.is_active','1')
                        ->where('employees.is_delete','0')
                        ->where('incidents.cls_inc_id',2)
                        ->whereIn('employees.id',$lEmployees)
                        ->where(function ($query) use ($sStartDate,$sEndDate) {
                                return $query->whereBetween('start_date', [$sStartDate,$sEndDate])
                                ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                        })
                        ->select('employees.id AS idEmp','incidents_day.date as Date')
                        ->get();
        $vacaciones = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('incidents_day','incidents_day.incidents_id','=','incidents.id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->whereIn('employees.id',$lEmployees)
                        ->where('incidents.cls_inc_id',3)
                        ->where(function ($query) use ($sStartDate,$sEndDate) {
                                return $query->whereBetween('start_date', [$sStartDate,$sEndDate])
                                ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                        })
                        ->select('employees.id AS idEmp','incidents_day.date as Date')
                        ->get();
        $inasistencia = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('incidents_day','incidents_day.incidents_id','=','incidents.id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->whereIn('employees.id',$lEmployees)
                        ->where('incidents.cls_inc_id',1)
                        ->where(function ($query) use ($sStartDate,$sEndDate) {
                                return $query->whereBetween('start_date', [$sStartDate,$sEndDate])
                                ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                        })
                        ->select('employees.id AS idEmp','incidents_day.date as Date','incidents.type_incidents_id as tipo')
                        ->get();
        $incidencias = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('incidents_day','incidents_day.incidents_id','=','incidents.id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->whereIn('employees.id',$lEmployees)
                        ->where(function ($query) use ($sStartDate,$sEndDate) {
                                return $query->whereBetween('start_date', [$sStartDate,$sEndDate])
                                ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                        })
                        ->select('employees.id AS idEmp','incidents_day.date as Date','incidents.type_incidents_id as tipo')
                        ->get();
        return view('report.reportView')
                    ->with('sTitle', 'Reporte de checadas')
                    ->with('lRows', $lRows)
                    ->with('incapacidades',$incapacidades)
                    ->with('vacaciones',$vacaciones)
                    ->with('inasistencia',$inasistencia)
                    ->with('incidencias',$incidencias)
                    ->with('reporttype',$request->reportType)
                    ->with('tipo', $tipoDatos)
                    ->with('payWay',$payWay);
    } 

    public function prueba(){
        $start = '2020-05-01';
        $end = '2020-05-15';
        $way = 1;
        $year = '2020';    
        $employees[0] = 67;
        

        //$employees[0] = 24;
        $key[0] = 2;
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

    public function reporteRevisionView(){
        return view('report.reportRevisionView');
    }

    public function generarReporteRevision(Request $request){
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;

        $inicio = Carbon::parse($sStartDate);
        $fin = Carbon::parse($sEndDate);
        $diferencia = ($inicio->diffInDays($fin));

        $empleadosSemanal = DB::table('employees')
                                ->join('jobs','jobs.id','=','employees.job_id')
                                ->join('departments','departments.id','=','jobs.department_id')
                                ->whereIn('dept_group_id',[1,4,6,7,8,9])
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',2)
                                ->select('employees.id AS id')
                                ->get();
        $empleadosQuincenal = DB::table('employees')
                                ->join('jobs','jobs.id','=','employees.job_id')
                                ->join('departments','departments.id','=','jobs.department_id')
                                ->whereIn('dept_group_id',[1,4,6,7,8,9])
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',1)
                                ->select('employees.id AS id')
                                ->get();
        
        
        
        
        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sEndDate);
        
        $auxContador = 0;
        $j = 0;
        $i = 0;
        $lRow = [];
        $lProg = [];
        for( $x = 0 ; count($empleadosSemanal) > $x ; $x++ ){
            $programado = false;
            $empleado = DB::table('employees')
                        ->where('id',$empleadosSemanal[$x]->id)
                        ->get();
            $registrosEntrada = DB::table('registers')
                        ->join('employees','employees.id','=','registers.employee_id')
                        ->where('employee_id',$empleadosSemanal[$x]->id)
                        ->where('type_id',1)
                        ->where('registers.is_delete',0)
                        ->whereBetween('date',[$sStartDate,$sEndDate])
                        ->groupBy('date')
                        ->select('date AS date','employee_id AS id','employees.name AS name')
                        ->get();
            $registrosSalida = DB::table('registers')
                        ->where('employee_id',$empleadosSemanal[$x]->id)
                        ->where('type_id',2)
                        ->whereBetween('date',[$sStartDate,$sEndDate])
                        ->groupBy('date')
                        ->get();
            $asignacion = DB::table('schedule_assign')
                    ->where('is_delete',0)
                    ->where('employee_id',$empleadosSemanal[$x]->id)
                    ->where('start_date','<=',$sStartDate)
                    ->where(function ($query) use ($sStartDate,$sEndDate) {
                        return $query->where('start_date','<=',$sStartDate)
                        ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                    })
                    ->get();
            
            $programacion = DB::table('week_department_day')
                    ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                    ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                    ->join('workshifts','day_workshifts.workshift_id','=','workshifts.id')
                    ->join('type_day','day_workshifts_employee.type_day_id','=','type_day.id')
                    ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                    ->where('employees.id',$empleadosSemanal[$x]->id)
                    ->where('week_department_day.date',$sStartDate)
                    ->get();
            if(count($asignacion) > 0 || count($programacion) > 0){
                $programado = true;
            }       
            $j = $auxContador;
            $i = 0;
            $idEmpleado = $empleadosSemanal[$x]->id;
            $nameEmpleado = $empleado[0]->name;
            $lProg[$x] = $programado;
            while( $auxFin >= $auxIni ){
                $row = new SReg();
                if($i < count($registrosEntrada)){
                    $auxComparacion = Carbon::parse($registrosEntrada[$i]->date);    
                    
                    if( $auxIni == $auxComparacion ){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = true;
                        $i++;
                        $auxIni->addDay();
                    }else if($auxIni < $auxComparacion){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = false;
                        $auxIni->addDay();
                    }
                    
                }else{
                    $row->idEmployee = $idEmpleado;
                    $row->nameEmployee = $nameEmpleado;
                    $row->date = $auxIni->toDateString();
                    $row->entrada = false;
                    $auxIni->addDay();
                }
                $lRow [$j] = $row;
                $j++;
            }
            $auxIni = Carbon::parse($sStartDate);
            $i = 0;
            $j = $auxContador;
            while( $auxFin >= $auxIni ){
                if($i < count($registrosSalida)){
                    $auxComparacion = Carbon::parse($registrosSalida[$i]->date);    
                    
                    if( $auxIni == $auxComparacion ){
                        $lRow [$j]->salida = true;
                        $i++;
                        $auxIni->addDay();
                    }else if($auxIni < $auxComparacion){
                        
                        $lRow [$j]->salida = false;
                        $auxIni->addDay();
                    }
                    
                }else{
                    $lRow [$j]->salida = false;
                    $auxIni->addDay();
                }
                $j++;
            }
            $auxContador = $j;
            $auxIni = Carbon::parse($sStartDate);
        } 
        //$lEmpSem = SGenUtils::toEmployeeIds(0, 0, null, $lEmpSem);
        //$lEmpQui = SGenUtils::toEmployeeIds(0, 0, null, $lEmpQui);
        $numEmpleados = count($empleadosSemanal);
        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sEndDate);
        
        $auxContador = 0;
        $j = 0;
        $i = 0;
        $lRow1 = [];
        $lProg1 = [];
        for( $x = 0 ; count($empleadosQuincenal) > $x ; $x++ ){
            $programado = false;
            $empleado = DB::table('employees')
                        ->where('id',$empleadosQuincenal[$x]->id)
                        ->get();
            $registrosEntrada = DB::table('registers')
                        ->join('employees','employees.id','=','registers.employee_id')
                        ->where('employee_id',$empleadosQuincenal[$x]->id)
                        ->where('type_id',1)
                        ->where('registers.is_delete',0)
                        ->whereBetween('date',[$sStartDate,$sEndDate])
                        ->groupBy('date')
                        ->select('date AS date','employee_id AS id','employees.name AS name')
                        ->get();
            $registrosSalida = DB::table('registers')
                        ->where('employee_id',$empleadosQuincenal[$x]->id)
                        ->where('type_id',2)
                        ->where('registers.is_delete',0)
                        ->whereBetween('date',[$sStartDate,$sEndDate])
                        ->groupBy('date')
                        ->get();
            $asignacion = DB::table('schedule_assign')
                    ->where('is_delete',0)
                    ->where('employee_id',$empleadosQuincenal[$x]->id)
                    ->where('start_date','<=',$sStartDate)
                    ->where(function ($query) use ($sStartDate,$sEndDate) {
                        return $query->where('start_date','<=',$sStartDate)
                        ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                    })
                    ->get();
            
            $programacion = DB::table('week_department_day')
                    ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                    ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                    ->join('workshifts','day_workshifts.workshift_id','=','workshifts.id')
                    ->join('type_day','day_workshifts_employee.type_day_id','=','type_day.id')
                    ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                    ->where('employees.id',$empleadosQuincenal[$x]->id)
                    ->where('week_department_day.date',$sStartDate)
                    ->get();
            if(count($asignacion) > 0 || count($programacion) > 0){
                $programado = true;
            }       
            $j = $auxContador;
            $i = 0;
            $idEmpleado = $empleadosQuincenal[$x]->id;
            $nameEmpleado = $empleado[0]->name;
            $lProg1[$x] = $programado;
            while( $auxFin >= $auxIni ){
                $row = new SReg();
                if($i < count($registrosEntrada)){
                    $auxComparacion = Carbon::parse($registrosEntrada[$i]->date);    
                    
                    if( $auxIni == $auxComparacion ){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = true;
                        $i++;
                        $auxIni->addDay();
                    }else if($auxIni < $auxComparacion){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = false;
                        $auxIni->addDay();
                    }
                    
                }else{
                    $row->idEmployee = $idEmpleado;
                    $row->nameEmployee = $nameEmpleado;
                    $row->date = $auxIni->toDateString();
                    $row->entrada = false;
                    $auxIni->addDay();
                }
                $lRow1 [$j] = $row;
                $j++;
            }
            $auxIni = Carbon::parse($sStartDate);
            $i = 0;
            $j = $auxContador;
            while( $auxFin >= $auxIni ){
                if($i < count($registrosSalida)){
                    $auxComparacion = Carbon::parse($registrosSalida[$i]->date);    
                    
                    if( $auxIni == $auxComparacion ){
                        $lRow1 [$j]->salida = true;
                        $i++;
                        $auxIni->addDay();
                    }else if($auxIni < $auxComparacion){
                        
                        $lRow1 [$j]->salida = false;
                        $auxIni->addDay();
                    }
                    
                }else{
                    $lRow1 [$j]->salida = false;
                    $auxIni->addDay();
                }
                $j++;
            }
            $auxContador = $j;
            $auxIni = Carbon::parse($sStartDate);
        } 
        //$lEmpSem = SGenUtils::toEmployeeIds(0, 0, null, $lEmpSem);
        //$lEmpQui = SGenUtils::toEmployeeIds(0, 0, null, $lEmpQui);
        $numEmpleados1 = count($empleadosQuincenal);
        //$lRowsSem = SDataProcess::process($sStartDate, $sEndDate, 2, $lEmpSem);
        //$lRowsQui = SDataProcess::process($sStartDate, $sEndDate, 1, $lEmpQui);
        $dateini = date_create($sStartDate);
        $datefin = date_create($sEndDate);
        $fechaAux1=date_format($dateini, 'd-m-Y');
        $fechaAux2=date_format($datefin, 'd-m-Y');
        return view('report.reportRevision')->with('lRows',$lRow)->with('lRows1',$lRow1)->with('inicio',$fechaAux1)->with('fin',$fechaAux2)->with('diff',$diferencia)->with('numEmpleados',$numEmpleados)->with('numEmpleados1',$numEmpleados1)->with('programado',$lProg)->with('programado1',$lProg1);
    }

    public function reporteCheckView(){
        return view('report.reportCheckView');
    }

    public function generarReporteCheck(Request $request){
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;

        $inicio = Carbon::parse($sStartDate);
        $fin = Carbon::parse($sEndDate);
        $diferencia = ($inicio->diffInDays($fin));

        $empleadosSemanal = DB::table('employees')
                                ->join('jobs','jobs.id','=','employees.job_id')
                                ->join('departments','departments.id','=','jobs.department_id')
                                ->whereIn('dept_group_id',[1,4,6,7,8,9])
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',2)
                                ->select('employees.id AS id')
                                ->get();
        $empleadosQuincenal = DB::table('employees')
                                ->join('jobs','jobs.id','=','employees.job_id')
                                ->join('departments','departments.id','=','jobs.department_id')
                                ->whereIn('dept_group_id',[1,4,6,7,8,9])
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',1)
                                ->select('employees.id AS id')
                                ->get();
        
        
        
        
        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sEndDate);
        
        $auxContador = 0;
        $j = 0;
        $i = 0;
        $lRow = [];
        $lProg = [];
        for( $x = 0 ; count($empleadosSemanal) > $x ; $x++ ){
            $programado = false;
            $empleado = DB::table('employees')
                        ->where('id',$empleadosSemanal[$x]->id)
                        ->get();
            $registros = DB::table('registers')
                        ->join('employees','employees.id','=','registers.employee_id')
                        ->where('employee_id',$empleadosSemanal[$x]->id)
                        ->whereBetween('date',[$sStartDate,$sEndDate])
                        ->where('registers.is_delete',0)
                        ->groupBy('date')
                        ->select('date AS date','employee_id AS id','employees.name AS name')
                        ->get();
            $asignacion = DB::table('schedule_assign')
                    ->where('is_delete',0)
                    ->where('employee_id',$empleadosSemanal[$x]->id)
                    ->where('start_date','<=',$sStartDate)
                    ->where(function ($query) use ($sStartDate,$sEndDate) {
                        return $query->where('start_date','<=',$sStartDate)
                        ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                    })
                    ->get();
            
            $programacion = DB::table('week_department_day')
                    ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                    ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                    ->join('workshifts','day_workshifts.workshift_id','=','workshifts.id')
                    ->join('type_day','day_workshifts_employee.type_day_id','=','type_day.id')
                    ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                    ->where('employees.id',$empleadosSemanal[$x]->id)
                    ->where('week_department_day.date',$sStartDate)
                    ->get();
            if(count($asignacion) > 0 || count($programacion) > 0){
                $programado = true;
            }       
            $j = $auxContador;
            $i = 0;
            $idEmpleado = $empleadosSemanal[$x]->id;
            $nameEmpleado = $empleado[0]->name;
            $lProg[$x] = $programado;
            while( $auxFin >= $auxIni ){
                $row = new SReg();
                if($i < count($registros)){
                    $auxComparacion = Carbon::parse($registros[$i]->date);    
                    
                    if( $auxIni == $auxComparacion ){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = true;
                        $i++;
                        $auxIni->addDay();
                    }else if($auxIni < $auxComparacion){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = false;
                        $auxIni->addDay();
                    }
                    
                }else{
                    $row->idEmployee = $idEmpleado;
                    $row->nameEmployee = $nameEmpleado;
                    $row->date = $auxIni->toDateString();
                    $row->entrada = false;
                    $auxIni->addDay();
                }
                $lRow [$j] = $row;
                $j++;
            }
            
            $auxContador = $j;
            $auxIni = Carbon::parse($sStartDate);
        } 
        //$lEmpSem = SGenUtils::toEmployeeIds(0, 0, null, $lEmpSem);
        //$lEmpQui = SGenUtils::toEmployeeIds(0, 0, null, $lEmpQui);
        $numEmpleados = count($empleadosSemanal);
        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sEndDate);
        
        $auxContador = 0;
        $j = 0;
        $i = 0;
        $lRow1 = [];
        $lProg1 = [];
        for( $x = 0 ; count($empleadosQuincenal) > $x ; $x++ ){
            $programado = false;
            $empleado = DB::table('employees')
                        ->where('id',$empleadosQuincenal[$x]->id)
                        ->get();
            $registros = DB::table('registers')
                        ->join('employees','employees.id','=','registers.employee_id')
                        ->where('employee_id',$empleadosQuincenal[$x]->id)
                        ->whereBetween('date',[$sStartDate,$sEndDate])
                        ->groupBy('date')
                        ->select('date AS date','employee_id AS id','employees.name AS name')
                        ->get();
            $asignacion = DB::table('schedule_assign')
                    ->where('is_delete',0)
                    ->where('employee_id',$empleadosQuincenal[$x]->id)
                    ->where('start_date','<=',$sStartDate)
                    ->where(function ($query) use ($sStartDate,$sEndDate) {
                        return $query->where('start_date','<=',$sStartDate)
                        ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                    })
                    ->get();
            
            $programacion = DB::table('week_department_day')
                    ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                    ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                    ->join('workshifts','day_workshifts.workshift_id','=','workshifts.id')
                    ->join('type_day','day_workshifts_employee.type_day_id','=','type_day.id')
                    ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                    ->where('employees.id',$empleadosQuincenal[$x]->id)
                    ->where('week_department_day.date',$sStartDate)
                    ->get();
            if(count($asignacion) > 0 || count($programacion) > 0){
                $programado = true;
            }       
            $j = $auxContador;
            $i = 0;
            $idEmpleado = $empleadosQuincenal[$x]->id;
            $nameEmpleado = $empleado[0]->name;
            $lProg1[$x] = $programado;
            while( $auxFin >= $auxIni ){
                $row = new SReg();
                if($i < count($registros)){
                    $auxComparacion = Carbon::parse($registros[$i]->date);    
                    
                    if( $auxIni == $auxComparacion ){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = true;
                        $i++;
                        $auxIni->addDay();
                    }else if($auxIni < $auxComparacion){
                        $row->idEmployee = $idEmpleado;
                        $row->nameEmployee = $nameEmpleado;
                        $row->date = $auxIni->toDateString();
                        $row->entrada = false;
                        $auxIni->addDay();
                    }
                    
                }else{
                    $row->idEmployee = $idEmpleado;
                    $row->nameEmployee = $nameEmpleado;
                    $row->date = $auxIni->toDateString();
                    $row->entrada = false;
                    $auxIni->addDay();
                }
                $lRow1 [$j] = $row;
                $j++;
            }
            
            $auxContador = $j;
            $auxIni = Carbon::parse($sStartDate);
        } 
        //$lEmpSem = SGenUtils::toEmployeeIds(0, 0, null, $lEmpSem);
        //$lEmpQui = SGenUtils::toEmployeeIds(0, 0, null, $lEmpQui);
        $numEmpleados1 = count($empleadosQuincenal);
        //$lRowsSem = SDataProcess::process($sStartDate, $sEndDate, 2, $lEmpSem);
        //$lRowsQui = SDataProcess::process($sStartDate, $sEndDate, 1, $lEmpQui);
        $dateini = date_create($sStartDate);
        $datefin = date_create($sEndDate);
        $fechaAux1=date_format($dateini, 'd-m-Y');
        $fechaAux2=date_format($datefin, 'd-m-Y');
        return view('report.reportCheck')->with('lRows',$lRow)->with('lRows1',$lRow1)->with('inicio',$fechaAux1)->with('fin',$fechaAux2)->with('diff',$diferencia)->with('numEmpleados',$numEmpleados)->with('numEmpleados1',$numEmpleados1)->with('programado',$lProg)->with('programado1',$lProg1);
    }

    public function reporteNumRegisterView(){
        return view('report.reportNumRegisterView');
    }

    public function generarReporteNumRegister(Request $request){
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;

        $inicio = Carbon::parse($sStartDate);
        $fin = Carbon::parse($sEndDate);
        $diferencia = ($inicio->diffInDays($fin));

        $empleadosSemanal = DB::table('employees')
                                ->join('jobs','jobs.id','=','employees.job_id')
                                ->join('departments','departments.id','=','jobs.department_id')
                                ->whereIn('dept_group_id',[1,4,6,7,8,9])
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',2)
                                ->select('employees.id AS id')
                                ->get();
        $empleadosQuincenal = DB::table('employees')
                                ->join('jobs','jobs.id','=','employees.job_id')
                                ->join('departments','departments.id','=','jobs.department_id')
                                ->whereIn('dept_group_id',[1,4,6,7,8,9])
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',1)
                                ->select('employees.id AS id')
                                ->get();
        
        
        
        
        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sEndDate);
        
        $auxContador = 0;
        $j = 0;
        $i = 0;
        $lRow = [];
        $lProg = [];
        for( $x = 0 ; count($empleadosSemanal) > $x ; $x++ ){
            $programado = false;
            $empleado = DB::table('employees')
                        ->where('id',$empleadosSemanal[$x]->id)
                        ->get();
            $asignacion = DB::table('schedule_assign')
                    ->where('is_delete',0)
                    ->where('employee_id',$empleadosSemanal[$x]->id)
                    ->where('start_date','<=',$sStartDate)
                    ->where(function ($query) use ($sStartDate,$sEndDate) {
                        return $query->where('start_date','<=',$sStartDate)
                        ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                    })
                    ->get();
            
            $programacion = DB::table('week_department_day')
                    ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                    ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                    ->join('workshifts','day_workshifts.workshift_id','=','workshifts.id')
                    ->join('type_day','day_workshifts_employee.type_day_id','=','type_day.id')
                    ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                    ->where('employees.id',$empleadosSemanal[$x]->id)
                    ->where('week_department_day.date',$sStartDate)
                    ->get();
            if(count($asignacion) > 0 || count($programacion) > 0){
                $programado = true;
            }       
            $j = $auxContador;
            $i = 0;
            $idEmpleado = $empleadosSemanal[$x]->id;
            $nameEmpleado = $empleado[0]->name;
            $lProg[$x] = $programado;
            while( $auxFin >= $auxIni ){
                $row = new SReg();   
                    
                $row->nameEmployee = $nameEmpleado;
                $row->date = $auxIni->toDateString();
                $fecha = $auxIni->toDateString();
                $numeroEntrada = DB::table('registers')
                                    ->where('date',$fecha)
                                    ->where('employee_id',$idEmpleado)
                                    ->where('registers.is_delete',0)
                                    ->where('type_id',1)
                                    ->select(DB::raw('COUNT(id) as numero'))
                                    ->get();
                $numeroSalida = DB::table('registers')
                                    ->where('date',$fecha)
                                    ->where('employee_id',$idEmpleado)
                                    ->where('registers.is_delete',0)
                                    ->where('type_id',2)
                                    ->select(DB::raw('COUNT(id) as numero'))
                                    ->get();
                if(isset($numeroEntrada[0])){
                    $row->num_entrada = $numeroEntrada[0]->numero;
                }else{
                    $row->num_entrada = 0;
                }
                if(isset($numeroSalida[0])){
                    $row->num_salida = $numeroSalida[0]->numero; 
                }else{
                    $row->num_salida = 0;
                }     
                $auxIni->addDay();
                
                $lRow [$j] = $row;
                $j++;
            }
            
            $auxContador = $j;
            $auxIni = Carbon::parse($sStartDate);
        } 
        //$lEmpSem = SGenUtils::toEmployeeIds(0, 0, null, $lEmpSem);
        //$lEmpQui = SGenUtils::toEmployeeIds(0, 0, null, $lEmpQui);
        $numEmpleados = count($empleadosSemanal);
        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sEndDate);
        
        $auxContador = 0;
        $j = 0;
        $i = 0;
        $lRow1 = [];
        $lProg1 = [];
        for( $x = 0 ; count($empleadosQuincenal) > $x ; $x++ ){
            $programado = false;
            $empleado = DB::table('employees')
                        ->where('id',$empleadosQuincenal[$x]->id)
                        ->get();
            $asignacion = DB::table('schedule_assign')
                    ->where('is_delete',0)
                    ->where('employee_id',$empleadosQuincenal[$x]->id)
                    ->where('start_date','<=',$sStartDate)
                    ->where(function ($query) use ($sStartDate,$sEndDate) {
                        return $query->where('start_date','<=',$sStartDate)
                        ->orwhereBetween('end_date', [$sStartDate,$sEndDate]);
                    })
                    ->get();
            
            $programacion = DB::table('week_department_day')
                    ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                    ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                    ->join('workshifts','day_workshifts.workshift_id','=','workshifts.id')
                    ->join('type_day','day_workshifts_employee.type_day_id','=','type_day.id')
                    ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                    ->where('employees.id',$empleadosQuincenal[$x]->id)
                    ->where('week_department_day.date',$sStartDate)
                    ->get();
            if(count($asignacion) > 0 || count($programacion) > 0){
                $programado = true;
            }       
            $j = $auxContador;
            $i = 0;
            $idEmpleado = $empleadosQuincenal[$x]->id;
            $nameEmpleado = $empleado[0]->name;
            $lProg1[$x] = $programado;
            while( $auxFin >= $auxIni ){
                $row = new SReg();   
                    
                $row->nameEmployee = $nameEmpleado;
                $row->date = $auxIni->toDateString();
                $fecha = $auxIni->toDateString();
                $numeroEntrada = DB::table('registers')
                                    ->where('date',$fecha)
                                    ->where('employee_id',$idEmpleado)
                                    ->where('registers.is_delete',0)
                                    ->where('type_id',1)
                                    ->select(DB::raw('COUNT(id) as numero'))
                                    ->get();
                $numeroSalida = DB::table('registers')
                                    ->where('date',$fecha)
                                    ->where('employee_id',$idEmpleado)
                                    ->where('registers.is_delete',0)
                                    ->where('type_id',2)
                                    ->select(DB::raw('COUNT(id) as numero'))
                                    ->get();
                if(isset($numeroEntrada[0])){
                    $row->num_entrada = $numeroEntrada[0]->numero;
                }else{
                    $row->num_entrada = 0;
                }
                if(isset($numeroSalida[0])){
                    $row->num_salida = $numeroSalida[0]->numero; 
                }else{
                    $row->num_salida = 0;
                }     
                $auxIni->addDay();
                
                $lRow1 [$j] = $row;
                $j++;
            }
            
            $auxContador = $j;
            $auxIni = Carbon::parse($sStartDate);
        } 
        //$lEmpSem = SGenUtils::toEmployeeIds(0, 0, null, $lEmpSem);
        //$lEmpQui = SGenUtils::toEmployeeIds(0, 0, null, $lEmpQui);
        $numEmpleados1 = count($empleadosQuincenal);
        //$lRowsSem = SDataProcess::process($sStartDate, $sEndDate, 2, $lEmpSem);
        //$lRowsQui = SDataProcess::process($sStartDate, $sEndDate, 1, $lEmpQui);
        $dateini = date_create($sStartDate);
        $datefin = date_create($sEndDate);
        $fechaAux1=date_format($dateini, 'd-m-Y');
        $fechaAux2=date_format($datefin, 'd-m-Y');
        return view('report.reportNumRegister')->with('lRows',$lRow)->with('lRows1',$lRow1)->with('inicio',$fechaAux1)->with('fin',$fechaAux2)->with('diff',$diferencia)->with('numEmpleados',$numEmpleados)->with('numEmpleados1',$numEmpleados1)->with('programado',$lProg)->with('programado1',$lProg1);
    }
    
        // Reporte de incidencias
        public function incidentReportView(){
            $incidents = typeincident::select('id','name')->get();
            $deptos = DepartmentRH::select('id','name')->where('is_delete',0)->get();
            $employees = employees::select('id','name','num_employee')->where('is_delete',0)->get();

            return view('report.incidentReport')->with('incidents',$incidents)->with('deptos',$deptos)->with('employees',$employees);
        }
   
        public function incidentReportGenerar(Request $request){
            $sStartDate = $request->start_date;
            $sEndDate = $request->end_date; 
            
            $employee = $request->employees;
            $department = $request->dept;
            $tipo = $request->tipo;
            $incident = $request->incidents;

            switch($tipo){
                //Caso de departamentos
                case 1:
                    $datas = DB::table('incidents')
                                    ->join('employees','incidents.employee_id','=','employees.id')
                                    ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                    ->join('class_incident','class_incident.id','=','incidents.cls_inc_id')
                                    ->join('type_incidents','type_incidents.id', '=', 'incidents.type_incidents_id')
                                    ->whereBetween('incidents.start_date',[$sStartDate,$sEndDate])
                                    ->whereBetween('incidents.end_date',[$sStartDate,$sEndDate])
                                    ->where('dept_rh.id',$department) 
                                    ->orderBy('incidents.start_date')
                                    ->select('incidents.start_date AS fechaI','incidents.end_date AS fechaF','type_incidents.name AS tipo','dept_rh.name AS departamento');
                    break;
                //Caso de empleados
                case 2:
                    if($employee != 0){
                        $datas = DB::table('incidents')
                                    ->join('employees','incidents.employee_id','=','employees.id')
                                    ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                    ->join('class_incident','class_incident.id','=','incidents.cls_inc_id')
                                    ->join('type_incidents','type_incidents.id', '=', 'incidents.type_incidents_id')
                                    ->whereBetween('incidents.start_date',[$sStartDate,$sEndDate])
                                    ->whereBetween('incidents.end_date',[$sStartDate,$sEndDate])
                                    ->where('employees.id',$employee) 
                                    ->orderBy('incidents.start_date')
                                    ->select('incidents.start_date AS fechaI','incidents.end_date AS fechaF','type_incidents.name AS tipo','employees.name AS empleado'); 
                    }else{
                        $datas = DB::table('incidents')
                                    ->join('employees','incidents.employee_id','=','employees.id')
                                    ->join('class_incident','class_incident.id','=','incidents.cls_inc_id')
                                    ->join('type_incidents','type_incidents.id', '=', 'incidents.type_incidents_id')
                                    ->whereBetween('incidents.start_date',[$sStartDate,$sEndDate])
                                    ->whereBetween('incidents.end_date',[$sStartDate,$sEndDate])
                                    ->orderBy('incidents.start_date')
                                    ->select('incidents.start_date AS fechaI','incidents.end_date AS fechaF','type_incidents.name AS tipo','employees.name AS empleado');              
                    }
                    break;
            }
            //Si selecciono una incidencia en particular
            if($incident != 0){
                $datas = $datas->where('incidents.type_incidents_id',$employee)->get();
            }else{
                $datas = $datas->get();
            }
        

            return view('report.incidentReportGenerar')->with('datas',$datas)->with('tipo',$tipo);
        }

        public function reporteUsoPuertas(){
            $rez = biostarController::login();

            if ($rez == null) {
                return null;
            }

            $headers = [
                'Content-Type' => 'application/json',
                'bs-session-id' => $rez,
                'Accept-Encoding' => 'gzip, deflate, br'
            ];

            $config = \App\SUtils\SConfiguration::getConfigurations(); 
        
        
            $client = new GuzzleClient([
                // Base URI is used with relative requests
                'base_uri' => $config->urlBiostar."/api/",
                // You can set any number of default request options.
                'timeout'  => 5.0,
                'headers' => $headers,
                'verify' => false
            ]);
            
            $r = $client->request('GET', 'devices', []);
            $response = $r;
            $response = $r->getBody()->getContents();
            $datas = json_decode($response);
            $reportType = 1;
            $lEmployees = employees::selectRaw('CONCAT(name, " - ", num_employee) AS name, id')->where('is_active', 1)->pluck('id', 'name');
            $lAreas = area::where('is_delete', 0)->pluck('id','name');
            return view('report.usopuertasdatos')->with('datas',$datas)->with('lEmployees',$lEmployees)->with('lAreas',$lAreas);
        }

        public function generarReportePuertas(Request $request){
            $rez = biostarController::login();
            $Sempleados = "";
            $Sdevice = "";
            $fecha_ini = Carbon::parse($request->start_date);
            $fecha_ini = $fecha_ini->toISOString();
            $fecha_fin = Carbon::parse($request->end_date);
            $fecha_fin->addHours(23);
            $fecha_fin = $fecha_fin->toISOString();
            if ($rez == null) {
                return null;
            }
            switch($request->tipo){
                case 1: 
                    $employees = DB::table('employees')
                                        ->join('departments','departments.id','=','employees.department_id')
                                        ->join('areas','areas.id','=','departments.area_id')
                                        ->whereIn('departments.area_id',$request->areas)
                                        ->where('employees.is_active',1)
                                        ->where('employees.biostar_id','>',0)
                                        ->select('employees.biostar_id AS biostar')
                                        ->get();
                    for($i = 0 ; count($employees) > $i ; $i++){
                        if($i == 0){
                            $Sempleados = $Sempleados.$employees[$i]->biostar;
                        }else{
                            $Sempleados = $Sempleados . '","' . $employees[$i]->biostar;   
                        }
                    }
                    break;
                case 2:
                    $employees = DB::table('employees')
                                        ->whereIn('employees.id',$request->employees)
                                        ->select('employees.biostar_id AS biostar')
                                        ->get();
                    for($i = 0 ; count($employees) > $i ; $i++){
                        if($i == 0){
                            $Sempleados = $Sempleados.$employees[$i]->biostar;
                        }else{
                            $Sempleados = $Sempleados . '","' . $employees[$i]->biostar;   
                        }
                    }
                    break;
            }

            for($i = 0 ; count($request->devices) > $i ; $i++){
                if($i == 0){
                    $Sdevice = $Sdevice.$request->devices[$i];
                }else{
                    $Sdevice = $Sdevice . '","' . $request->devices[$i];   
                }
            }

            $headers = [
                'Content-Type' => 'application/json',
                'bs-session-id' => $rez,
                'Accept-Encoding' => 'gzip, deflate, br'
            ];

            $config = \App\SUtils\SConfiguration::getConfigurations(); 
        
        
            $client = new GuzzleClient([
                // Base URI is used with relative requests
                'base_uri' => $config->urlBiostar."/api/",
                // You can set any number of default request options.
                'timeout'  => 5.0,
                'headers' => $headers,
                'verify' => false
            ]);
            
            $body = '{
                "Query": {
                  "limit": 10000000,
                  "conditions": [
                    {
                      "column": "event_type_id.code",
                      "operator": 2,
                      "values": [
                        "4865"
                      ]
                    },
                    {
                      "column": "datetime",
                      "operator": 3,
                      "values": [
                        "'.$fecha_ini.'", "'.$fecha_fin.'"
                      ]
                    },
                    {
                      "column":"device_id.id",
                      "operator": 2,
                      "values": [
                        "'.$Sdevice.'"
                      ]
                    },
                    {
                        "column":"user_id.user_id",
                        "operator": 2,
                        "values": [
                          "'.$Sempleados.'"
                        ]
                    }
                  ],
                  "orders": [
                    {
                      "column": "datetime",
                      "descending": false
                    }
                  ]
                }
              }';
            
            $r = $client->request('POST', 'events/search', [
                'body' => $body
            ]);
            $response = $r;
            $response = $r->getBody()->getContents();
            $data = json_decode($response);
            $orden = $request->orden;

            return view('report.reportepuerta')->with('data',$data)->with('orden',$orden);
        }

        public function indexFaltasReport(){
            $deptos = DepartmentRH::select('id','name')->where('is_delete',0)->get();
            $employees = employees::select('id','name', 'num_employee')->where('is_delete',0)->get();

            return view('report.reporteFaltas', ['deptos' => $deptos, 'employees' => $employees]);
        }

        public function FaltasReportGenerar(Request $request){
            $calendarStart = getDate(strtotime($request->calendarStart));
            $calendarEnd = getDate(strtotime($request->calendarEnd));
            $date = date_create($calendarStart['year'] . '-' . $calendarStart['mon'] . '-' . $calendarStart['mday']);
            $start = date_format($date,"Y-m-d");
            $date = date_create($calendarEnd['year'] . '-' . $calendarEnd['mon'] . '-' . cal_days_in_month(CAL_GREGORIAN,$calendarEnd['mon'],$calendarEnd['year']));
            $end = date_format($date,"Y-m-d");
            $datetime1 = date_create($start);
            $datetime2 = date_create($end);
            
            $interval = (Integer)date_diff($datetime1, $datetime2)->format('%m');
            
            $meses = [['nombre'=>'Enero','mes'=>'01'],['nombre'=>'Febrero','mes'=>'02'],['nombre'=>'Marzo','mes'=>'03'],
                        ['nombre'=>'Abril','mes'=>'04'],['nombre'=>'Mayo','mes'=>'05'],['nombre'=>'Junio','mes'=>'06'],
                        ['nombre'=>'Julio','mes'=>'07'],['nombre'=>'Agosto','mes'=>'08'],['nombre'=>'Septiembre','mes'=>'09'],
                        ['nombre'=>'Octubre','mes'=>'10'],['nombre'=>'Noviembre','mes'=>'11'],['nombre'=>'Diciembre','mes'=>'12']];

            $range = [];

            if($interval != 0){
                $interval = $interval + ((Integer)$calendarStart['mon'] - 1);
                for ($i=(Integer)$calendarStart['mon'] - 1; $i <= $interval ; $i++) { 
                    array_push($range, (object)$meses[$i]);
                }
                
            }else{
                array_push($range, (object)$meses[(Integer)$calendarStart['mon'] - 1]);
            }

            switch($request->tipo){
                //Caso de departamentos
                case 1:
                    $employees = \DB::table('employees')
                            ->leftJoin('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                            ->where('employees.department_id', '!=', 100)
                            ->where('employees.admission_date', '<=', $end)
                            ->where('dept_rh.id',$request->dept)
                            ->select('employees.name AS empleado','employees.id AS empleado_id',
                                    'dept_rh.name AS departamento','employees.num_employee as num',
                                    'employees.is_active as active', 'employees.admission_date as admission',
                                    'employees.leave_date as leave')
                            ->get();

                    $data = \DB::table('incidents_day')
                                ->join('incidents','incidents.id', '=', 'incidents_day.incidents_id')
                                ->join('employees','incidents.employee_id','=','employees.id')
                                ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                ->join('class_incident','class_incident.id','=','incidents.cls_inc_id')
                                ->join('type_incidents','type_incidents.id', '=', 'incidents.type_incidents_id')
                                ->whereBetween('incidents.start_date', [$start,$end])
                                ->whereBetween('incidents.end_date',[$start,$end])
                                ->where('dept_rh.id',$request->dept)
                                ->whereIn('incidents.type_incidents_id', [1, 4, 5, 6])
                                ->where('incidents.is_delete', false)
                                ->where('employees.is_delete', false)
                                ->orderBy('incidents.start_date')
                                ->select('incidents_day.date AS fechaI',
                                        'dept_rh.name AS departamento', 'employees.name AS empleado','employees.id AS empleado_id',
                                        'employees.num_employee as num', 'employees.is_active as active', 'employees.admission_date as admission');
                    
                    $processed = \DB::table('processed_data')
                                    ->join('employees','processed_data.employee_id','=','employees.id')
                                    ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                    ->whereBetween('processed_data.outDate', [$start,$end])
                                    ->where('dept_rh.id',$request->dept)
                                    ->where('processed_data.hasabsence', 1)
                                    ->where('employees.is_delete', false)
                                    ->orderBy('processed_data.outDate')
                                    ->select('processed_data.outDate AS fechaI',
                                            'dept_rh.name AS departamento', 
                                            'employees.name AS empleado','employees.id AS empleado_id',
                                            'employees.num_employee as num', 'employees.is_active as active', 
                                            'employees.admission_date as admission');
                                    
                    break;
                //Caso de empleados
                case 2:
                    if($request->employees != 0){
                        $employees = \DB::table('employees')
                        ->leftJoin('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                        ->where('employees.department_id', '!=', 100)
                        ->where('employees.admission_date', '<=', $end)
                        ->where('employees.id',$request->employees)
                        ->select('employees.name AS empleado','employees.id AS empleado_id',
                                'dept_rh.name AS departamento','employees.num_employee as num',
                                'employees.is_active as active', 'employees.admission_date as admission',
                                'employees.leave_date as leave')
                        ->get();

                        $data = \DB::table('incidents_day')
                                    ->join('incidents','incidents.id', '=', 'incidents_day.incidents_id')
                                    ->join('employees','incidents.employee_id','=','employees.id')
                                    ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                    ->join('class_incident','class_incident.id','=','incidents.cls_inc_id')
                                    ->join('type_incidents','type_incidents.id', '=', 'incidents.type_incidents_id')
                                    ->whereBetween('incidents.start_date', [$start,$end])
                                    ->whereBetween('incidents.end_date',[$start,$end])
                                    ->where('employees.id',$request->employees)
                                    ->whereIn('incidents.type_incidents_id', [1, 4, 5, 6])
                                    ->where('incidents.is_delete', false)
                                    ->where('employees.is_delete', false)
                                    ->orderBy('incidents.start_date')
                                    ->select('incidents_day.date AS fechaI',
                                            'dept_rh.name AS departamento', 'employees.name AS empleado','employees.id AS empleado_id',
                                            'employees.num_employee as num', 'employees.is_active as active', 'employees.admission_date as admission');

                        $processed = \DB::table('processed_data')
                                        ->join('employees','processed_data.employee_id','=','employees.id')
                                        ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                        ->whereBetween('processed_data.outDate', [$start,$end])
                                        ->where('employees.id',$request->employees)
                                        ->where('processed_data.hasabsence', 1)
                                        ->where('employees.is_delete', false)
                                        ->orderBy('processed_data.outDate')
                                        ->select('processed_data.outDate AS fechaI',
                                                'dept_rh.name AS departamento', 
                                                'employees.name AS empleado','employees.id AS empleado_id',
                                                'employees.num_employee as num', 'employees.is_active as active', 
                                                'employees.admission_date as admission');
                    }else{
                        $employees = \DB::table('employees')
                        ->leftJoin('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                        ->where('employees.department_id', '!=', 100)
                        ->where('employees.admission_date', '<=', $end)
                        ->select('employees.name AS empleado','employees.id AS empleado_id',
                                'dept_rh.name AS departamento','employees.num_employee as num',
                                'employees.is_active as active', 'employees.admission_date as admission',
                                'employees.leave_date as leave')
                        ->get();

                        $data = \DB::table('incidents_day')
                                    ->join('incidents','incidents.id', '=', 'incidents_day.incidents_id')
                                    ->join('employees','incidents.employee_id','=','employees.id')
                                    ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                    ->join('class_incident','class_incident.id','=','incidents.cls_inc_id')
                                    ->join('type_incidents','type_incidents.id', '=', 'incidents.type_incidents_id')
                                    ->whereBetween('incidents.start_date', [$start,$end])
                                    ->whereBetween('incidents.end_date',[$start,$end])
                                    ->whereIn('incidents.type_incidents_id', [1, 4, 5, 6])
                                    ->where('incidents.is_delete', false)
                                    ->where('employees.is_delete', false)
                                    ->orderBy('incidents.start_date')
                                    ->select('incidents_day.date AS fechaI',
                                            'dept_rh.name AS departamento', 'employees.name AS empleado','employees.id AS empleado_id',
                                            'employees.num_employee as num', 'employees.is_active as active', 'employees.admission_date as admission');

                        $processed = \DB::table('processed_data')
                                        ->join('employees','processed_data.employee_id','=','employees.id')
                                        ->join('dept_rh','dept_rh.id','=','employees.dept_rh_id')
                                        ->whereBetween('processed_data.outDate', [$start,$end])
                                        ->where('processed_data.hasabsence', 1)
                                        ->where('employees.is_delete', false)
                                        ->orderBy('processed_data.outDate')
                                        ->select('processed_data.outDate AS fechaI',
                                                'dept_rh.name AS departamento', 
                                                'employees.name AS empleado','employees.id AS empleado_id',
                                                'employees.num_employee as num', 'employees.is_active as active', 
                                                'employees.admission_date as admission');
                    }
                    break;
            }
            
            $actualEmployees = $employees->where('leave', null);
            $leaveEmployees = $employees->where('leave', '!=', null);

            $ReturnEmployees = $leaveEmployees->filter(function ($item) {
                return (data_get($item, 'leave') < data_get($item, 'admission'));
            });

            $actualLeaveEmployees = $leaveEmployees->filter(function ($item) use($start) {
                return (data_get($item, 'leave') > data_get($item, 'admission')) && (data_get($item, 'leave') > $start);
            });

            $totEmployees = $actualEmployees->merge($ReturnEmployees);
            $totEmployees = $totEmployees->merge($actualLeaveEmployees);
            $data = $data->get();
            $processed = $processed->get();
            $merged = $data->merge($processed);
            
            return view('report.reporteFaltasView',  ['data' => $merged, 'range' => $range, 'totEmployees' => $totEmployees, 'calendarStart' => $calendarStart]);
        }

        public function reportIncidentsEmployees(){
            $config = \App\SUtils\SConfiguration::getConfigurations();

            $bDirect = false;
            $payType = 0;
            $bDelegation = null;
            $subEmployees = SPrepayrollUtils::getEmployeesByUser(\Auth::user()->id, $bDirect, $payType, $bDelegation);
            if ($subEmployees == null) {
                $lEmployees = SGenUtils::toEmployeeIds(0, 0, []);
            }
            else {
                $qEmployees = SGenUtils::toEmployeeQuery(0, 0, []);

                $lEmployees = $qEmployees->whereIn('e.id', $subEmployees)
                                ->orderBy('e.name', 'ASC')
                                ->get();
            }

            return view('report.reportIncidentsEmployees')
                        ->with('tReport', \SCons::REP_HR_EX)
                        ->with('sRoute', 'reporteIncidenciasEmpleadosGenerar')
                        ->with('lEmployees', $lEmployees)
                        ->with('startOfWeek', $config->startOfWeek);
        }

        public function reportIncidentsEmployeesGenerar(Request $request)
        {
            $sStartDate = $request->start_date;
            $sEndDate = $request->end_date;
            $iEmployee = $request->emp_id;

            $oStartDate = Carbon::parse($sStartDate);
            $oEndDate = Carbon::parse($sEndDate);

            $diff_days = $oStartDate->diffInDays($oEndDate);

            if (! $oStartDate->lessThanOrEqualTo($oEndDate)) {
                return \Redirect::back()->withErrors(['Error', 'La fecha de inicio debe ser previa a la fecha final']);
            }

            if ($request->optradio == "employee") {
                if ($iEmployee > 0) {
                    $lEmployees = SGenUtils::toEmployeeIds(0, 0, 0, [$iEmployee]);
                    $payWay = $lEmployees[0]->way_pay_id;
                }
                else {
                    return \Redirect::back()->withErrors(['Error', 'Debe seleccionar empleado']);
                }
            }
            else {
                if (session()->get('rol_id') != 1){
                    $dgu = DB::table('group_dept_user')
                            ->where('user_id',auth()->user()->id)
                            ->select('groupdept_id AS id')
                            ->get();
                    $Adgu = [];
                    for($i=0;count($dgu)>$i;$i++){
                        $Adgu[$i]=$dgu[$i]->id;
                    }
                    
                    $employee = DB::table('employees')
                            ->join('jobs','jobs.id','=','employees.job_id')
                            ->join('departments','departments.id','=','employees.department_id')
                            ->join('department_group','department_group.id','=','departments.dept_group_id')
                            ->whereIn('departments.dept_group_id',$Adgu)
                            ->where('employees.is_active',1)
                            ->select('employees.id AS id')
                            ->get();
                }else{
                    $employee = DB::table('employees')
                            ->join('jobs','jobs.id','=','employees.job_id')
                            ->join('departments','departments.id','=','employees.department_id')
                            ->join('department_group','department_group.id','=','departments.dept_group_id')
                            ->where('employees.is_active',1)
                            ->orderBy('employees.name')
                            ->select('employees.id AS id')
                            ->get();
                }
                /**
                 * 1: quincena
                 * 2: semana
                 * 3: todos
                 */
                $payWay = $request->pay_way == null ? \SCons::PAY_W_S : $request->pay_way;

                $filterType = $request->i_filter;
                $ids = $request->elems;
                $aEmpl = [];
                foreach($employee as $emp){
                    array_push($aEmpl, $emp->id);
                }
                $lEmployees = SGenUtils::toEmployeeIds($payWay, $filterType, $ids, $aEmpl);
            }

            $emplIds = $lEmployees->pluck('id');

            $incidents = \DB::table('incidents as in')
                            ->leftJoin('incidents_day as in_d', 'in_d.incidents_id','=','in.id')
                            ->leftJoin('type_incidents as tp', 'tp.id','=','in.type_incidents_id')
                            ->where('in.is_delete',0)
                            ->whereBetween('in.start_date',[$sStartDate,$sEndDate])
                            ->whereIn('in.employee_id',$emplIds)
                            ->select(
                                'in.id','in.type_incidents_id','in.cls_inc_id','start_date','end_date',
                                'in.nts','in.employee_id','in.is_delete','in_d.date','tp.name'
                            )
                            ->get();

            $lRows = [];
            
            foreach ($lEmployees as $emp) {
                $oDate = Carbon::parse($sStartDate);
                $date = $oDate->format('Y-m-d');
                for ($i=0; $i <= $diff_days; $i++) {
                    array_push($lRows, (object)[
                        'employee' => $emp->name,
                        'num_employee' => substr((string)($emp->num_employee + 1000000), 1),
                        'employee_id' => $emp->id,
                        'date' => $date,
                        'incident' => collect($incidents->where('employee_id', $emp->id)->where('date',$date)->first())->get('name'),
                        'incident_type' => collect($incidents->where('employee_id', $emp->id)->where('date',$date)->first())->get('type_incidents_id')
                    ]);
                    $date = $oDate->addDay()->format('Y-m-d');
                }
            }
            
            $route = route('reporteIncidenciasEmpleados');

            return view('report.reportIncidentsEmployeesView', ['lRows' => $lRows, 'sStartDate' => $sStartDate, 'sEndDate' => $sEndDate, 'route' => $route]);
        }
}
