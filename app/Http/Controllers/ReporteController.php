<?php

namespace App\Http\Controllers;

use App\Http\Controllers\incidentController;
use App\Http\Controllers\PrepayrollReportController;
use App\Models\area;
use App\Models\department;
use App\Models\DepartmentRH;
use App\Models\departmentsGroup;
use App\Models\employees;
use App\Models\incidentDay;
use App\Models\PrepayReportControl;
use App\Models\prepayrollAdjType;
use App\Models\prepayrollAdjust;
use App\Models\PrepayrollDelegation;
use App\Models\incident;
use App\Models\typeincident;
use App\SData\SDataProcess;
use App\SUtils\SDateTimeUtils;
use App\SUtils\SDateUtils;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SGenUtils;
use App\SUtils\SHolidayWork;
use App\SUtils\SInfoWithPolicy;
use App\SUtils\SPayrollDelegationUtils;
use App\SUtils\SPermissions;
use App\SUtils\SPrepayrollUtils;
use App\SUtils\SReg;
use App\SUtils\SReportsUtils;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteController extends Controller
{
    /**
     * Recibe el tipo de reporte y en base a este retorna una colección con los valores posibles
     *
     * @param integer $reportType
     *                  1  Reporte por área
     *                  2  Reporte por grupo de departamentos
     *                  3  Reporte por departamentos
     *                  4  Reporte por empleados
     * 
     * @return \Illuminate\View\View
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
                                    ->orderBy('e.name')
                                    ->orderBy('date')
                                    ->orderBy('time');
                break;
            case 5:
                $register = $register->whereIn('e.id', $values)
                                    ->select('e.id', 'e.num_employee', 'e.name', 'r.date', 'r.time', 'r.type_id', 'e.external_id')
                                    ->groupBy('e.name','date','type_id','e.num_employee')
                                    ->orderBy('e.name')
                                    ->orderBy('date')
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
     * @return \Illuminate\View\View
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
     * @return \Illuminate\View\View report.reportRegsView
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
        $subEmployees = SPrepayrollUtils::getEmployeesByUser(\Auth::user()->id, $payType, $bDirect, $bDelegation);
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

    public function genHrExReportDelegations($id = 0)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $payType = 0;
        $bDirect = 0;
        $iDelegations = 0;
        $oPayrolls = SPayrollDelegationUtils::getDelegationsPayrolls($id == 0 ? \Auth::user()->id : $id);

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
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
                    ->with('lRows', $lRows)
                    ->with('lComments', []);
    }

    /**
     * Muestra reporte de tiempos extra
     *
     * @param Request $request
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function hrExtReport(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;
        $iEmployee = $request->emp_id;
        $reportMode = $request->report_mode;
        $bDelegation = $request->delegation;
        $idDelegation = $request->id_delegation;

        if (isset($request->wizard)) {
            $wizard = $request->wizard;
            $filter_employees = $request->filter_employee;
        } else {
            $wizard = 0;
            $filter_employees = 0;
        }

        $lCommentsppAdjsTypes = \DB::table('prepayroll_adjusts_types')
                                    ->select('id')
                                    ->get();
        $lCommentsAdjsTypes = [];
        foreach ($lCommentsppAdjsTypes as $adjType) {
            $lCommentsAdjsTypes[$adjType->id] = \DB::table('prepayroll_adjusts_comments AS pac')
                                            ->join('comments AS c', 'pac.comment_id', '=', 'c.id')
                                            ->where('c.is_delete', 0)
                                            ->where('pac.adjust_type_id', $adjType->id)
                                            ->select('c.id', 'c.comment')
                                            ->get();
        }

        $oStartDate = Carbon::parse($sStartDate);
        $oEndDate = Carbon::parse($sEndDate);

        if (! $oStartDate->lessThanOrEqualTo($oEndDate)) {
            return \Redirect::back()->withErrors(['Error', 'La fecha de inicio debe ser previa a la fecha final']);
        }

        if($request->optradio == "employee"){
            $oEmployee = \DB::table('employees')
                            ->where('id', $iEmployee)
                            ->first();

            if (! is_null($oEmployee)) {
                $request->pay_way = $oEmployee->way_pay_id;
            }
        }
        
        $numIni = sDateUtils::getNumberOfDate($sStartDate, $request->pay_way == null ? \SCons::PAY_W_S : $request->pay_way);
        $numFin = sDateUtils::getNumberOfDate($sEndDate, $request->pay_way == null ? \SCons::PAY_W_S : $request->pay_way);
        if (($numIni[1] != $numFin[1]) || $numIni[0] > $numFin[0]) {
            return \Redirect::back()->withErrors(['Error', 'No se puede generar un reporte que abarca más de un año']);
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

        if ($bDelegation) {
            $oDelegation = PrepayrollDelegation::find($idDelegation);
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

            $dates = SDateUtils::getDatesOfPayrollNumber($oDelegation->number_prepayroll, $oDelegation->year, $payWay);
            $sStartDate = $dates[0];
            $sEndDate = $dates[1];
            $oStartDate = Carbon::parse($sStartDate);
            $oEndDate = Carbon::parse($sEndDate);
        }

        $roles = Auth()->user()->roles()->get();
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $seeAll = false;
        foreach ($roles as $rol) {
            if (in_array($rol->id, $config->rolesCanSeeAll)) {
                $seeAll = true;
                break;
            }
        }

        $bDirect = false;
        $subEmployees = SPrepayrollUtils::getEmployeesByUser(\Auth::user()->id, $payWay, $bDirect, $idDelegation);
        if (! is_null($subEmployees) && count($subEmployees) >= 0) {
            $lColEmps = collect($lEmployees);
            if(!$seeAll){
                $lEmployees = $lColEmps->whereIn('id', $subEmployees);
            }
        }

        $lEmployees = SReportsUtils::filterEmployeesByAdmissionDate($lEmployees, $sEndDate, 'id');

        /******************************************************************************************************** 
         * Proceso de prenómina
        */

        $lRows = SDataProcess::process($sStartDate, $sEndDate, $payWay, $lEmployees);

        /******************************************************************************************************* */

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
                                    'pa.id',
                                    'pa.apply_time'
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

        $isAdmin = false;
        foreach (auth()->user()->roles()->get() as $rol) {
            $result = in_array($rol->id, $config->rolesCanSeeAll);
            if ($result) {
                $isAdmin = true;
                break;
            }
        }

        $subEmployees = [];
        if (!$isAdmin) {
            $dirEmpl = SPrepayrollUtils::getEmployeesByUser(auth()->user()->id, 0, true, null);
            foreach ($dirEmpl as $data) {
                array_push($subEmployees, $data);
            }
            $lUsers = null;
        }
        else {
            $lUsers = DB::table('users')
                ->join('prepayroll_groups_users as pru','pru.head_user_id','=','users.id')
                ->select('users.id','users.name')
                ->orderBy('users.name')
                ->get();
        }
        
        /**
         * Obtención de vobos de empleados
         */
        $isPrepayrollInspection = false;
        $lEmpVobos = [];
        $aNumber = [];
        if (($payWay == \SCons::PAY_W_S || $payWay == \SCons::PAY_W_Q) && env('VOBO_BY_EMP_ENABLED', true) && $wizard > 0) {
            $aNumber = SDateUtils::getNumberOfDate($sStartDate, $payWay);
            $dates = SDateUtils::getDatesOfPayrollNumber($aNumber[0], $aNumber[1], $payWay);
            
            if ($dates[0] == $sStartDate && $dates[1] == $sEndDate) {
                $lEmpVobos = DB::table('prepayroll_report_emp_vobos AS evb')
                                    ->join('users AS u', 'evb.vobo_by_id', '=', 'u.id')
                                    ->join('employees AS e', 'evb.employee_id', '=', 'e.id')
                                    ->where('evb.is_delete', 0)
                                    ->where('year', $aNumber[1])
                                    ->select('u.name AS user_name', 'evb.employee_id', 'evb.vobo_by_id', 'e.num_employee');

                if ($payWay == \SCons::PAY_W_Q) {
                    $lEmpVobos = $lEmpVobos->where('evb.is_biweek', true)
                                            ->where('evb.num_biweek', $aNumber[0]);
                }
                else {
                    $lEmpVobos = $lEmpVobos->where('evb.is_week', true)
                                            ->where('evb.num_week', $aNumber[0]);
                }

                $lEmpVobos = $lEmpVobos->get()->keyBy('num_employee')->toArray();

                $isPrepayrollInspection = true;
            }
        }

        if ($reportMode == \SCons::REP_HR_EX) {
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
                    ->with('lCommentsAdjsTypes', $lCommentsAdjsTypes)
                    ->with('subEmployees', $subEmployees)
                    ->with('isAdmin', $isAdmin)
                    ->with('lUsers', $lUsers)
                    ->with('wizard', $wizard )
                    ->with('filter_employees',$filter_employees)
                    ->with('pay_way', $request->pay_way)
                    ->with('bDelegation', $bDelegation)
                    ->with('idDelegation', $idDelegation);
        }
        else {
            $listaEmployees = SReportsUtils::resumeReportRows($lRows, $lEmployees);
            $col = collect($lRows);

            foreach ($listaEmployees as $emp) {
                $oCom = $lAdjusts->where('employee_id', $emp->id)->all();
                $arr = [];
                foreach ($oCom as $com) {
                    array_push($arr, $com->dt_date . ", " . $com->comments);
                }
                $emp->comments = $arr;
                $emp->scheduleText = $col->where('idEmployee', $emp->id)->first()->scheduleText;
            }
            
            $routePrev = route('checkPrevius_vobos');
            $routeChildren = route('checkChildrens_vobos');
            $oPrepayrollCtrl = null;
            if ($isPrepayrollInspection) {
                $oPrepayrollCtrl =  DB::table('prepayroll_report_auth_controls AS prac')
                                                    ->join('users AS u', 'prac.user_vobo_id', '=', 'u.id')
                                                    ->select('prac.*', 'u.name AS username')
                                                    ->where('prac.user_vobo_id', \Auth::user()->id)
                                                    ->where('prac.year', $aNumber[1])
                                                    ->where(($payWay == \SCons::PAY_W_S ? 'prac.is_week' : 'prac.is_biweek'), true)
                                                    ->where(($payWay == \SCons::PAY_W_S ? 'prac.num_week' : 'prac.num_biweek'), $aNumber[0])
                                                    ->where('prac.is_delete', 0)
                                                    ->first();
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
                    ->with('lEmpVobos', $lEmpVobos)
                    ->with('isPrepayrollInspection', $isPrepayrollInspection)
                    ->with('routeChildren', $routeChildren)
                    ->with('routePrev', $routePrev)
                    ->with('registriesRoute', route('registro_ajuste'))
                    ->with('lRows', $lRows)
                    ->with('lCommentsAdjsTypes', array())
                    ->with('lEmployees', $listaEmployees)
                    ->with('subEmployees', $subEmployees)
                    ->with('isAdmin', $isAdmin)
                    ->with('lUsers', $lUsers)
                    ->with('wizard', $wizard )
                    ->with('filter_employees',$filter_employees)
                    ->with('pay_way', $request->pay_way)
                    ->with('oPrepayrollCtrl', $oPrepayrollCtrl)
                    ->with('idPreNomina', $payWay == \SCons::PAY_W_Q ? "biweek" : "week")
                    ->with('bDelegation', $bDelegation)
                    ->with('idDelegation', $idDelegation);
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
                                    ->select('incidents.start_date AS fechaI','incidents.end_date AS fechaF','type_incidents.name AS tipo','dept_rh.name AS departamento', 'employees.name AS empleado');
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

                    for( $i = 0 ; count($request->areas) > $i ; $i++){
                        if( $request->areas[$i] == 0 ){
                            $areas = 0;
                        }else{
                            $areas = 1;
                        }
                    }

                    if($areas == 0){
                        $employees = DB::table('employees')
                                        ->join('departments','departments.id','=','employees.department_id')
                                        ->join('areas','areas.id','=','departments.area_id')
                                        ->where('employees.is_active',1)
                                        ->where('employees.biostar_id','>',0)
                                        ->select('employees.biostar_id AS biostar')
                                        ->get();
                    }else{
                        $employees = DB::table('employees')
                                        ->join('departments','departments.id','=','employees.department_id')
                                        ->join('areas','areas.id','=','departments.area_id')
                                        ->whereIn('departments.area_id',$request->areas)
                                        ->where('employees.is_active',1)
                                        ->where('employees.biostar_id','>',0)
                                        ->select('employees.biostar_id AS biostar')
                                        ->get();
                    }
                    
                    for($i = 0 ; count($employees) > $i ; $i++){
                        if($i == 0){
                            $Sempleados = $Sempleados.$employees[$i]->biostar;
                        }else{
                            $Sempleados = $Sempleados . '","' . $employees[$i]->biostar;   
                        }
                    }
                    break;
                case 2:

                    for( $i = 0 ; count($request->employees) > $i ; $i++){
                        if( $request->employees[$i] == 0 ){
                            $employees = 0;
                        }else{
                            $employees = 1;
                        }
                    }

                    if($employees == 0){
                        $employees = DB::table('employees')
                                        ->where('employees.is_active',1)
                                        ->where('employees.biostar_id','>',0)
                                        ->select('employees.biostar_id AS biostar')
                                        ->get();
                    }else{
                        $employees = DB::table('employees')
                                        ->whereIn('employees.id',$request->employees)
                                        ->select('employees.biostar_id AS biostar')
                                        ->get();
                    }
                    
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

        public function reportIncidentsEmployees($wizard = 0, $bDelegation = null) {
            // wizard = 1 -> funcionamiento independiente de la vista.
            // wizard = 2 -> funcionamiento en conjunto de las vistas para hacer un wizard de revisión prenómina.
            $config = \App\SUtils\SConfiguration::getConfigurations();

            $bDirect = false;
            $payType = 0;
            $subEmployees = SPrepayrollUtils::getEmployeesByUser(\Auth::user()->id, $bDirect, $payType, null);
            if ($subEmployees == null) {
                $lEmployees = SGenUtils::toEmployeeIds(0, 0, []);
            }
            else {
                $qEmployees = SGenUtils::toEmployeeQuery(0, 0, []);

                $lEmployees = $qEmployees->whereIn('e.id', $subEmployees)
                                ->orderBy('e.name', 'ASC')
                                ->get();
            }

            $viewName = "report.reportIncidentsEmployees";
            $oPayrolls = null;

            if ($wizard == 2 && $bDelegation) {
                $oPayrolls = SPayrollDelegationUtils::getDelegationsPayrolls(\Auth::user()->id);

                if (count($oPayrolls->weeks) == 0 && count($oPayrolls->biweeks) == 0) {
                    return redirect()->back()->withErrors(['No hay semanas o quincenas delegadas para ti.']);
                }

                $viewName = "report.reportWizardDelegation";
            }

            return view($viewName)
                    ->with('tReport', \SCons::REP_HR_EX)
                    ->with('sTitle', "Nóminas delegadas")
                    ->with('sRoute', 'reporteIncidenciasEmpleadosGenerar')
                    ->with('lEmployees', $lEmployees)
                    ->with('oPayrolls', $oPayrolls)
                    ->with('startOfWeek', $config->startOfWeek)
                    ->with('wizard', $wizard);
        }

        public function reportIncidentsEmployeesGenerar(Request $request)
        {
            $bDelegation = ! isset($request->delegation) ? false : isset($request->delegation);
            $iIdDelegation = isset($request->id_delegation) ? $request->id_delegation : null;
            //si no es el wizard entra en esta parte
            if ( $request->wizard != 2 ){
                $iEmployee = $request->emp_id;
            }

            if (! $bDelegation) {
                $sStartDate = $request->start_date;
                $sEndDate = $request->end_date;

                $oStartDate = Carbon::parse($sStartDate);
                $oEndDate = Carbon::parse($sEndDate);

                if (! $oStartDate->lessThanOrEqualTo($oEndDate)) {
                    return \Redirect::back()->withErrors(['Error', 'La fecha de inicio debe ser previa a la fecha final']);
                }
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
            
            if ($bDelegation) {
                $oDelegation = PrepayrollDelegation::find($iIdDelegation);
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

                $dates = SDateUtils::getDatesOfPayrollNumber($oDelegation->number_prepayroll, $oDelegation->year, $oDelegation->pay_way_id);
                $sStartDate = $dates[0];
                $sEndDate = $dates[1];
                $oStartDate = Carbon::parse($sStartDate);
                $oEndDate = Carbon::parse($sEndDate);
            }

            $roles = Auth()->user()->roles()->get();
            $config = \App\SUtils\SConfiguration::getConfigurations();
            $seeAll = false;
            foreach ($roles as $rol) {
                if (in_array($rol->id, $config->rolesCanSeeAll)) {
                    $seeAll = true;
                    break;
                }
            }

            $bDirect = false;
            $subEmployees = SPrepayrollUtils::getEmployeesByUser(\Auth::user()->id, $payWay, $bDirect, $iIdDelegation);
            if (! is_null($subEmployees) && count($subEmployees) >= 0) {
                $lColEmps = collect($lEmployees);
                if(!$seeAll){
                    $lEmployees = $lColEmps->whereIn('id', $subEmployees);
                }
            }

            $lEmployees = SReportsUtils::filterEmployeesByAdmissionDate($lEmployees, $sStartDate, 'id');

            // si es parte del wizard cambia la ruta
            if ($request->wizard != 2) {
                $route = route('reporteIncidenciasEmpleados', "['id' => 1]");
            }
            else {
                $route = route('reporteIncidenciasEmpleados', "['id' => 2]");
                //$routeSiguiente = route('wizardSiguiente1') ;
            }
            $routeStore = route('reporteIncidenciasEmpleadosStore');
            $routeDelete = route('reporteIncidenciasEmpleadosDelete');

            $lTypeIncidents = \DB::table('type_incidents')->get();

            $lRows = SDataProcess::process($sStartDate, $sEndDate, $payWay, $lEmployees);

            /**
             * Obtención de vobos de empleados
             */
            $isPrepayrollInspection = false;
            $lEmpVobos = [];
            $aNumber = [];
            if (($payWay == \SCons::PAY_W_S || $payWay == \SCons::PAY_W_Q) && env('VOBO_BY_EMP_ENABLED', true) && $request->wizard > 0) {
                $aNumber = SDateUtils::getNumberOfDate($sStartDate, $payWay);
                $dates = SDateUtils::getDatesOfPayrollNumber($aNumber[0], $aNumber[1], $payWay);
                
                if ($dates[0] == $sStartDate && $dates[1] == $sEndDate) {
                    $lEmpVobos = DB::table('prepayroll_report_emp_vobos AS evb')
                                        ->join('users AS u', 'evb.vobo_by_id', '=', 'u.id')
                                        ->join('employees AS e', 'evb.employee_id', '=', 'e.id')
                                        ->where('evb.is_delete', 0)
                                        ->where('year', $aNumber[1])
                                        ->select('u.name AS user_name', 'evb.employee_id', 'evb.vobo_by_id', 'e.num_employee');

                    if ($payWay == \SCons::PAY_W_Q) {
                        $lEmpVobos = $lEmpVobos->where('evb.is_biweek', true)
                                                ->where('evb.num_biweek', $aNumber[0]);
                    }
                    else {
                        $lEmpVobos = $lEmpVobos->where('evb.is_week', true)
                                                ->where('evb.num_week', $aNumber[0]);
                    }

                    $lEmpVobos = $lEmpVobos->get()->keyBy('employee_id')->toArray();

                    $isPrepayrollInspection = true;
                }
            }

            $aDates = [];
            $oDate = Carbon::parse($sStartDate);
            $oEndDate = Carbon::parse($sEndDate);
            while ($oDate->lessThanOrEqualTo($oEndDate)) {
                $aDates[] = $oDate->toDateString();
                $oDate->addDay();
            }

            $collRows = collect($lRows);
            $lReportRows = [];
            foreach ($lEmployees as $oEmployee) {
                $oRepRow = new \stdClass();
                $oRepRow->idEmployee = $oEmployee->id;
                $oRepRow->numEmployee = $oEmployee->num_employee;
                $oRepRow->nameEmployee = $oEmployee->name;
                $oRepRow->isVobo = $isPrepayrollInspection && array_key_exists($oEmployee->id, $lEmpVobos);
                $oRepRow->faltas = 0;
                $oRepRow->descansos = 0;
                $oRepRow->vacaciones = 0;
                $oRepRow->inasistencias = 0;
                $oRepRow->incapacidad = 0;
                $oRepRow->onomastico = 0;
                $oRepRow->days = [];

                $lEmpRows = $collRows->where('idEmployee', $oEmployee->id);
                foreach ($aDates as $sDate) {
                    $lEmpRs = collect($lEmpRows);
                    $lDay = $lEmpRs->where('outDate', $sDate);
                    $oRepRow->days[$sDate] = new \stdClass();

                    $events = [];
                    foreach ($lDay as $oRowDay) {
                        if ($oRowDay->hasAbsence) {
                            $oRepRow->days[$sDate]->hasAbsence = true;
                            $oRepRow->faltas++;
                        }

                        if (count($oRowDay->events) > 0) {
                            /**
                             * id: name, is_agreement, is_allowed
                             * 1: INASIST. S/PERMISO, [0][0]
                                2: INASIST. C/PERMISO S/GOCE, [0][0]
                                3: INASIST. C/PERMISO C/GOCE, [0][1]
                                4: INASIST. ADMTIVA. RELOJ CHECADOR, [0][0]
                                5: INASIST. ADMTIVA. SUSPENSIÓN, [0][0]
                                6: INASIST. ADMTIVA. OTROS, [0][0]
                                7: ONOMÁSTICO, [0][1]
                                8: Riesgo de trabajo, [0][1]
                                9: Enfermedad en general, [0][0]
                                10: Maternidad, [0][1]
                                11: Licencia por cuidados médicos de hijos diagnosticados con cáncer., [0][1]
                                12: VACACIONES, [0][1]
                                13: VACACIONES PENDIENTES, [0][1]
                                14: CAPACITACIÓN, [1][1]
                                15: TRABAJO FUERA PLANTA, [1][1]
                                16: PATERNIDAD, [0][1]
                                17: DIA OTORGADO, [1][1]
                                18: INASIST. PRESCRIPCION MEDICA, [0][1]
                                19: DESCANSO, [1][1]
                                20: INASIST. TRABAJO FUERA DE PLANTA, [1][1]
                                21: VACACIONES, [1][1]
                                22: INCAPACIDAD, [1][1]
                                23: ONOMÁSTICO, [1][1]
                                24: PERMISO, [1][0]
                             */ 
                            foreach ($oRowDay->events as $evt) {
                                $event = (object) $evt;
                                switch ($event->type_id) {
                                    case 1:
                                    case 2:
                                    case 3:
                                    case 4:
                                    case 5:
                                    case 6:
                                        $oRepRow->inasistencias++;
                                        break;
                                    case 12:
                                    case 13:
                                    case 21:
                                        $oRepRow->vacaciones++;
                                        break;
                                    case 7:
                                    case 23:
                                        $oRepRow->onomastico++;
                                        break;
                                    case 19:
                                        $oRepRow->descansos++;
                                        break;
                                    case 10:
                                    case 11:
                                    case 16:
                                    case 18:
                                    case 20:
                                    case 22:
                                        $oRepRow->incapacidad++;
                                        break;
                                    default:
                                        break;
                                }

                                $events[] = $event;
                            }
                        }
                    }

                    $oRepRow->days[$sDate]->events = $events;
                }

                $lReportRows[] = $oRepRow;
            }

            // Incidencias permitidas en CAP
            $lTypeCapIncidents = \DB::table('type_incidents')
                                        ->whereIn('id', [12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24])
                                        ->orderBy('name', 'ASC')
                                        ->get();
            
            return view('report.reportIncidentsEmployeesView', [
                                                            'lRows' => $lReportRows,
                                                            'sStartDate' => $sStartDate,
                                                            'sEndDate' => $sEndDate,
                                                            'route' => $route,
                                                            'lTypeIncidents' => $lTypeIncidents,
                                                            'lTypeCapIncidents' => $lTypeCapIncidents,
                                                            'routeStore' => $routeStore,
                                                            'routeDelete' => $routeDelete,
                                                            'aDates' => $aDates,
                                                            'wizard' => $request->wizard,
                                                            'payWay' => $payWay,
                                                            'subEmployees' => $subEmployees,
                                                            'bDelegation' => $bDelegation,
                                                            'iIdDelegation' => $iIdDelegation,
                                                            'isPrepayrollInspection' => $isPrepayrollInspection
                                                        ]);
        }

        public function reportIncidentsEmployeesStore(Request $request) {
            $incident = null;
            if (isset($request->id_incident) && $request->id_incident > 0) {
                $incident = incident::find($request->id_incident);
            }
            
            $incidentController = null;
            if (is_null($incident)) {
                $incident = new incident();
            }
            $incidentController = new incidentController();

            try {
                DB::transaction(function () use ($request, $incidentController, $incident) {
                    $incident->updated_by = session()->get('user_id');
                    $incident->nts = $request->comments;
                    $incident->type_incidents_id = $request->typeIncident;

                    if ($incident->id > 0) {
                        $incident->update();
                    }
                    else {
                        $incident->external_key = "0_0";
                        $incident->cls_inc_id = 1;
                        $incident->created_by = session()->get('user_id');
                        $incident->start_date = $request->date;
                        $incident->end_date = $request->date;
                        $incident->employee_id = $request->employee_id;
                        
                        $incident->save();
                    }

                    $incidentController->saveDays($incident);
                });

            }
            catch (\Throwable $e) {
                return redirect()->back()->with(['tittle' => 'Error', 'message' => 'Error al guardar el registro', 'icon' => 'error']);
            }

            return redirect()->back()->with(['tittle' => 'Realizado', 'message' => 'Registro guardado con exito', 'icon' => 'success']);
        }

        public function reportIncidentsEmployeesDelete(Request $request){
            $incident = null;
            if (isset($request->id_incident) && $request->id_incident > 0) {
                $incident = incident::find($request->id_incident);
            }

            try {
                DB::transaction(function () use ($request, $incident) {
                    if(! is_null($incident)) {
                        $incident->is_delete = 1;
                        $incident->update();
                        
                        $incidentController = new incidentController();
                        $incidentController->saveDays($incident);
                    }
                });
            } catch (\Throwable $e) {
                return redirect()->back()->with(['tittle' => 'Error', 'message' => 'Error al guardar el registro', 'icon' => 'error']);
            }

            return redirect()->back()->with(['tittle' => 'Realizado', 'message' => 'Registro guardado con exito', 'icon' => 'success']);
        }

        public function wizardSiguiente($pagina){
            if( $pagina == 1 ){

            }elseif( $pagina == 2){

            }
        }

        public function wizardAtras($pagina){
            if( $pagina == 2 ){

            }elseif( $pagina == 3 ){

            }
        }

        public function AccesoPuertasDatos(){
            $user = session()->get('id');
            $user = 59;
            // Query para sacar los devices asignados.
            $lDevices = DB::table('user_vs_device')
                            ->join('users','user_vs_device.user_id', '=', 'users.id')
                            ->join('devices','user_vs_device.device_id', '=', 'devices.id')
                            ->where('user_vs_device.is_delete',0)
                            ->where('user_id',$user)
                            ->orderBy('users.id')
                            ->select('devices.name AS dn','devices.code AS di')
                            ->get();

            return view('report.datosAccesoPuerta')->with('lDevices',$lDevices);
        }

        public function AccesoPuertasGenerar(Request $request){
            $rez = biostarController::login();
            $Sdevice = "";
            $Sempleados = "";
            $fecha_ini = Carbon::parse($request->start_date);
            $fecha_ini = $fecha_ini->toISOString();
            $fecha_fin = Carbon::parse($request->end_date);
            $fecha_fin->addHours(23);
            $fecha_fin = $fecha_fin->toISOString();
            if ($rez == null) {
                return null;
            }

            for($i = 0 ; count($request->devices) > $i ; $i++){
                if($i == 0){
                    $Sdevice = $Sdevice.$request->devices[$i];
                }else{
                    $Sdevice = $Sdevice . '","' . $request->devices[$i];   
                }
            }


            $employees = DB::table('employees')
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
            //$orden = $request->orden;

            return view('report.accesoPuerta')->with('data',$data);
        }
}
