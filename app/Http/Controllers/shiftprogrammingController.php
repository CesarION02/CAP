<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\workshift;
use App\Models\job;
use App\Models\employee;
use App\Models\week;
use App\Models\week_department;
use App\Models\week_department_day;
use App\Models\day_workshifts;
use App\Models\day_workshifts_employee;
use App\Models\pdf_week;
use App\SUtils\SDateTimeUtils;
use DateTime;
use DB;
use PDF;

class shiftprogrammingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {   
        //$typeArea = $id;
        $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
        $dgu = DB::table('group_dept_user')
                    ->where('user_id',$usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
        $typeArea = $dgu[0]->id;
            
        $week = DB::table('pdf_week')
                ->join('week','week.id','=','pdf_week.week_id')
                ->select('week.start_date AS start','week.end_date AS end','week.id AS id')
                ->get();
        $year = DB::table('pdf_week')
                ->join('week','week.id','=','pdf_week.week_id')
                ->groupBy('week.year')
                ->select('week.year AS year')
                ->get();
        $newest = week::where('is_delete','=',0)->orderBy('updated_at','desc')->first();
        if($newest != null){
            $fechaini = SDateTimeUtils::orderDate($newest->start_date);
            $fechafin = SDateTimeUtils::orderDate($newest->end_date);
        }else{
            $fechaini = null;
            $fechafin = null;
        }

        return view('shiftprogramming.index', compact('typeArea'),compact('newest'))->with('week',$week)->with('year',$year)->with('fechaini',$fechaini)->with('fechafin',$fechafin);

    }

    public function rhview(){
        $year = DB::table('pdf_week')
                ->join('week','week.id','=','pdf_week.week_id')
                ->groupBy('week.year')
                ->select('week.year AS year')
                ->get();
                
        return view('shiftprogramming.view', compact('year'));
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

    public function recoverPDF(Request $request){
        $namePdf = pdf_week::where('week_id','=',$request->semana)->where('is_delete','=','0')->orderBy('updated_at')->first();
        return response()->json($namePdf);
    }

    public function recoverWeek(Request $request){
        $weeks = DB::table('pdf_week')
        ->join('week','week.id','=','pdf_week.week_id')
        ->where('week.year',$request->anio)
        ->orderBy('week.week_number','DESC')
        ->select('week.start_date AS start','week.end_date AS end','week.id AS id','pdf_week.url AS nombre')
        ->get();

        return response()->json($weeks);
    }

    public function newShift(Request $request){
        $departments = DB::table('jobs')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('departments.id')
                        ->orderBy('jobs.id')
                        ->where('jobs.is_delete','0')
                        ->where('departments.is_delete','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','departments.id AS idDepartment','departments.name AS nameDepartment')
                        ->get();
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', '1')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','employees.name AS nameEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        $group_workshift = DB::table('group_workshifts')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift')
                        ->get();
        $startDate = $request->ini;
        $endDate = $request->fin;
        $vacaciones = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('incidents.cls_inc_id',3)
                        ->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereBetween('start_date', [$startDate, $endDate])
                                    ->orwhereBetween('end_date', [$startDate, $endDate]);
                        })
                        ->select('employees.name AS name')
                        ->get();
        $incapacidades = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('incidents.cls_inc_id',2)
                        ->where(function ($query) use ($startDate, $endDate) {
                            $query->whereBetween('start_date', [$startDate, $endDate])
                                ->orwhereBetween('end_date', [$startDate, $endDate]);
                        })
                        ->select('employees.name AS name')
                        ->get();
        return response()->json(array($employees,$departments,$group_workshift,$vacaciones,$incapacidades));

    }

    public function workShift(Request $request){
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.name')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', '1')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','employees.name AS nameEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        $workshifts = DB::table('group_workshifts_lines')
                        ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                        ->where('group_workshifts_id',$request->turno)
                        ->orderBy('workshifts.order_view')
                        ->select('workshifts.id AS idWork','workshifts.name AS nameWork')
                        ->get();
        $departments = DB::table('jobs')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->orderBy('jobs.id')
                        ->where('jobs.is_delete','0')
                        ->where('departments.id',$request->departamento)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','departments.id AS idDepartment','departments.name AS nameDepartment')
                        ->get();
        return response()->json(array($employees,$workshifts,$departments));
                
    }

    public function newRow(Request $request){
        $workshifts = DB::table('group_workshifts_lines')
                        ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                        ->where('group_workshifts_id',$request->turno)
                        ->orderBy('workshifts.order_view')
                        ->select('workshifts.id AS idWork')
                        ->get();
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.name')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', '1')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','employees.name AS nameEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();    
                        return response()->json(array($workshifts,$employees));
    }

    public function turnos(Request $request){
        $workshifts = DB::table('group_workshifts_lines')
                        ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                        ->orderBy('workshifts.order_view')
                        ->select('workshifts.id AS idWork', 'workshifts.name AS nameWork','workshifts.entry AS entry', 'workshifts.departure AS departure')
                        ->get();
        $startDate = $request->ini;
        $endDate = $request->fin;
        $vacaciones = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('incidents_day','incidents_day.incidents_id','=','incidents.id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('incidents.cls_inc_id',3)
                        ->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereBetween('start_date', [$startDate, $endDate])
                                ->orwhereBetween('end_date', [$startDate, $endDate]);
                        })
                        ->select('employees.id AS idEmp','incidents_day.date as Date')
                        ->get();
        $incapacidades = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('incidents_day','incidents_day.incidents_id','=','incidents.id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_active','1')
                        ->where('employees.is_delete','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('incidents.cls_inc_id',2)
                        ->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereBetween('start_date', [$startDate, $endDate])
                                ->orwhereBetween('end_date', [$startDate, $endDate]);
                        })
                        ->select('employees.id AS idEmp','incidents_day.date as Date')
                        ->get();
        $festivosDept = DB::table('holiday_assign')
                        ->join('departments','departments.id','=','holiday_assign.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('departments.is_delete','0')
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('holiday_assign.department_id')
                        ->select('departments.id AS idDept','holiday_assign.date as Date','departments.name AS name')
                        ->get();
        $festivosArea = DB::table('holiday_assign')
                        ->join('areas','areas.id','=','holiday_assign.area_id')
                        ->join('departments','departments.area_id','=','areas.id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('areas.is_delete','0')
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('holiday_assign.area_id')
                        ->groupBy('holiday_assign.area_id','date')
                        ->select('areas.id AS idArea','holiday_assign.date as Date','areas.name AS name')
                        ->get();
        $festivosEmployee = DB::table('holiday_assign')
                        ->join('employees', 'employees.id','=','holiday_assign.employee_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','0')
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('holiday_assign.employee_id')
                        ->select('employees.id AS idEmp','holiday_assign.date as Date','employees.name AS name')
                        ->get();


        return response()->json(array($workshifts,$vacaciones,$incapacidades,$festivosArea,$festivosDept,$festivosEmployee));
         
    }

    public function uploadimage(Request $request){
        if(is_array($_FILES)) 
        {
         if(is_uploaded_file($_FILES['userImage']['tmp_name'])) {
          $sourcePath = $_FILES['userImage']['tmp_name'];
          $targetPath = "images/".$_FILES['userImage']['name'];
          if(move_uploaded_file($sourcePath,$targetPath)) {
          ?>
           <?php
           echo $targetPath;
          }
         }
        }   
    }

    public function guardar(Request $request){
        $flagPDF =0;
        $deleteFlag = 0;
        $nombrePdf = 0;
        $typearea = $request->typeArea;
        if($request->weekFlag == 0){
            $week = new week();
        }else{
            $week = week::find($request->weekFlag);
        }
        $anio = explode('-',$request->ini);
        $week->week_number = $request->semana;
        $week->year = $anio[0];
        $week->start_date = $request->ini;
        $week->end_date = $request->fin;
        $week->is_delete = 0;
        $week->save();
        $data = $week->id;
        if($request->turnoflag == '0'){
            for($i = 0 ; count($request->departamento) > $i ; $i++ ){
                if($request->departFlag != 0 && $deleteFlag == 0  ){
                    $deletedRows = week_department::where('week_id', $week->id)->delete();
                    $deleteFlag = 1;
                }
                $week_department = new week_department();
                $week_department->week_id = $week->id;
                $week_department->department_id = $request->departamento[$i];
                $week_department->status = $request->cerrado[$i];
                $week_department->group_id = $request->turno[$i];
                $week_department->is_delete = 0;
                $week_department->save();
                $date1 = new DateTime($request->ini);
                $date2 = new DateTime($request->fin);
                $diff = $date1->diff($date2);
                $date = date($request->ini);
                if($request->cerrado[$i] == 1){
                    for($j = 0 ; $diff->days >=$j ; $j++){
                        $week_department_day = new week_department_day();
                        $week_department_day->date = $date;
                        $week_department_day->week_department_id = $week_department->id;
                        $week_department_day->status = $request->cerrado[$i];
                        $week_department_day->save();
                        $date = date($date);
                        $date = date("Y-m-d",strtotime($date."+1 days"));
                        $workshifts = DB::table('group_workshifts_lines')
                        ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                        ->where('group_workshifts_id',$request->turno[$i])
                        ->where('is_delete','=','0')
                        ->orderBy('workshifts.order')
                        ->select('workshifts.id AS idWork', 'workshifts.name AS nameWork')
                        ->get(); 

                        for( $x = 0 ;count($workshifts)>$x;$x++){
                            $day_workshifts = new day_workshifts();
                            $day_workshifts->name = 'na';
                            $day_workshifts->day_id = $week_department_day->id;
                            $day_workshifts->workshift_id = $workshifts[$x]->idWork;
                            $day_workshifts->is_delete = 0;
                            $day_workshifts->save();

                            for( $y = 0 ; count($request->Empleado) > $y ; $y++){
                                if($request->arrDept[$y] == $week_department->department_id && $request->arrTurno[$y] == $day_workshifts->workshift_id){
                                    for( $contEmpl =  0 ; count($request->arrCalendarioEmpleados) > $contEmpl ; $contEmpl++){
                                        if($request->arrCalendarioEmpleados[$contEmpl] == $request->Empleado[$y]){
                                            $day_workshifts_employee = new day_workshifts_employee;
                                            $day_workshifts_employee->employee_id = $request->Empleado[$y];
                                            $day_workshifts_employee->day_id = $day_workshifts->id;
                                            $day_workshifts_employee->job_id = $request->arrJob[$y];   
                                            $day_workshifts_employee->is_rest = 0;
                                            $day_workshifts_employee->type_day_id = $request->arrCalendarioDias[$contEmpl][$j];
                                            $day_workshifts_employee->is_delete = 0; 
                                            $day_workshifts_employee->save(); 
                                        }
                                    }
                                }
                                   
                            }
                        }


                    }
                }
               
                //$mod_date = strtotime($date."+ 2 days");
            }
            $flagPDF = 1;
        }
        if($flagPDF == 1){
            $this->pdf($week->id,$typearea);
            $codigo = DB::table('department_group')
                            ->where('id',$typearea)
                            ->get();
            
            $nombrePdf = 'RolTur'.$week->week_number.''.$week->year.''.$codigo[0]->code.'.pdf';
            if($request->pdfFlag == 0){
                $guardarPdf = new pdf_week();
                $guardarPdf->dept_group_id = $typearea;
            }else{
                $guardarPdf = pdf_week::where('week_id','=',$week->id)->where('dept_group_id','=',$typearea)->first();
            }
            
            $guardarPdf->week_id = $week->id;
            $guardarPdf->url = $nombrePdf;
            $guardarPdf->is_delete = 0;
            $guardarPdf->save();
            
        }
        return response()->json(array($data,$nombrePdf));

    }

    public function pdf($id,$typearea){
        $week = week::findOrFail($id);
        $codigo = DB::table('department_group')
                            ->where('id',$typearea)
                            ->get();
        $nombrePdf = 'RolTur'.$week->week_number.''.$week->year.''.$codigo[0]->code;
        $formateoIni = explode('-',$week->start_date);
        $fechaInicio = $formateoIni[2].'-'.$formateoIni[1].'-'.$formateoIni[0];
        $dias = array('','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo');
        $diasTitulo = array('','lun.','mar.','miér.','jue.','vier.','sáb.','dom.');
        $meses = array('','ene.','feb.','mar.','abr.','may.','jun.','jul.','ago.','sep.','oct.','nov.','dic.');
        $fini = $diasTitulo[date('N', strtotime($fechaInicio))];
        $formateoFin = explode('-',$week->end_date);
        $fechaFin = $formateoFin[2].'-'.$formateoFin[1].'-'.$formateoFin[0];
        $fin = $diasTitulo[date('N', strtotime($fechaFin))];
        $nombreMes = $meses[date('n', strtotime($fechaFin))];
        $config = \App\SUtils\SConfiguration::getConfigurations();
        
        /*Encabezado del pdf */
        $header = 
        '
            <table class = "container">
                <tbody>
                    <tr>
                        <th style  = "text-align: center; font-size: 0.4cm;">
                            '.$config->company.'
                        </th>
                    </tr>
                    <tr>
                        <th style  = "text-align: center; font-size: 0.4cm;">
                        Rol de turnos semanales del '.$fini.' '.$formateoIni[2]
                        .($formateoIni[1] != $formateoFin[1] ? ' de '.$meses[(int)$formateoIni[1]] : '')
                        .($formateoIni[0] != $formateoFin[0] ? ' del '.$formateoIni[0] : '')
                        .' al '.$fin.' '.$formateoFin[2].
                        ' de '.$nombreMes.' de '.$formateoFin[0].'
                        </th>
                    </tr>
                </tbody>
            </table>
        ';

        /*Pie de pagina del pdf */
        $footer = 
        '
            <table class = "container" style = "border-top: 0.03cm solid #000000;">
                <tbody>
                    <tr>
                        <td style = "width: 33%;">
                        </td>
                        <td class = "th3" style = "width: 33%; text-align: center;">
                            Página {PAGENO} de {nb}
                        </td>
                        <td class = "th3" style = "width: 33%; text-align: right;">
                            '.auth()->user()->name.'  '.date("d-m-Y h:i:sa").'
                        </td>
                    </tr>
                </tbody>
            </table>
        ';

        /*Cuerpo del pdf */
        $html = '';

        $grupo = DB::table('departments')
                    ->where('dept_group_id',$typearea)
                    ->where('is_delete',0)
                    ->select('id AS id')
                    ->get();
                    
        $Agrupo = [];
        for($x=0;count($grupo)>$x;$x++){
            $Agrupo[$x]=$grupo[$x]->id;
        }
        
        $departments = DB::table('week_department')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->where('week_id',$week->id)
                        ->whereIn('departments.id',$Agrupo)
                        ->select('departments.id AS idDepartment', 'departments.name AS nameDepartment','week_department.status AS status','week_department.group_id AS group')
                        ->get();
        for( $x = 0 ;count($departments)>$x;$x++){
            $table = 
            '
            <div class = "inLine-left" style = "padding-right: 0.2cm;">
                <table class = "border" style = "width: 100%;">
                    <tbody>
            ';
            $tdworkshifts = '';
            $tdworkshiftName = '';
            $th = '';
            if($departments[$x]->status == 2){
                $th = $th.'<th>'.$departments[$x]->nameDepartment.'</th>';
                $td = '<td class = "border">Cerrado</td>';
                $table = $table.'<tr>'.$th.'</tr><tr>'.$td.'</tr></tbody></table></div>';
            }else{
                $workshifts = DB::table('group_workshifts_lines')
                                ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                                ->where('group_workshifts_id',$departments[$x]->group)
                                ->where('is_delete','=','0')
                                ->orderBy('workshifts.order')
                                ->select('workshifts.id AS idWork', 'workshifts.name AS nameWork', 'workshifts.entry AS entry','workshifts.departure AS departure')
                                ->get();
                $job = DB::table('jobs')
                        ->where('jobs.department_id',$departments[$x]->idDepartment)
                        ->where('is_delete','=','0')
                        ->select('jobs.id AS idJob', 'jobs.name AS nameJob')
                        ->get();
                $numTurno = count($workshifts);
                $tamañoCol = 60/$numTurno;
                $th = $th.'<th colspan = "'.$numTurno.'" class = "border">'.$departments[$x]->nameDepartment.'</th>';
                for( $y = 0 ; $numTurno > $y ; $y++ ){
                    $tdworkshiftName = $tdworkshiftName.'<td class = "border">'.$workshifts[$y]->nameWork.'</td>';
                    $tdworkshifts = $tdworkshifts.'<td class = "border"><p style = "font-size: 0.3cm;">'.substr($workshifts[$y]->entry, 0, -3).' - '.substr($workshifts[$y]->departure, 0, -3).'</p></td>';
                }
                
                $empleados = DB::table('week')
                                ->join('week_department','week.id','=','week_department.week_id')
                                ->join('departments','week_department.department_id','=','departments.id')
                                ->join('week_department_day','week_department.id','=','week_department_day.week_department_id')
                                ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                                ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                                ->join('employees','day_workshifts_employee.employee_id','=','employees.id')
                                ->where('week_id','=',$week->id)
                                ->select('day_workshifts_employee.job_id AS idJob','employees.name AS nameEmployee','employees.short_name AS shortName','day_workshifts.workshift_id AS id')
                                ->groupBy('employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id')
                                ->orderBy('id', 'DESC')
                                ->get();
                                $tdbody = '';  
                for( $z = 0 ; count($job) > $z ; $z++ ){
                    $tdJob = '<tr><td colspan = "'.$numTurno.'" class = "border th2">'.$job[$z]->nameJob.'</td></tr>';
                    $tdbody = $tdbody.$tdJob;
                    $tdemploy = '';
                    for( $y = 0 ; count($empleados) > $y ; $y++){
                        $tdemploy = $tdemploy.'<tr>';
                        for( $j = 0 ; $numTurno > $j ; $j++){
                            $turnoEmpleado = true;
                            for( $i = 0 ; count($empleados) > $i ; $i++){
                                if($job[$z]->idJob == $empleados[$i]->idJob){
                                    if($workshifts[$j]->idWork == $empleados[$i]->id){
                                        $turnoEmpleado = true;
                                        if($empleados[$i]->shortName != ''){
                                            $tdemploy = $tdemploy.'<td class = "border"><p style = "font-size: 0.25cm">'.$empleados[$i]->shortName.'</p></td>';
                                            $empleados[$i]->id = 0;
                                            break;
                                        }else{
                                            $tdemploy = $tdemploy.'<td class = "border"><p style = "font-size: 0.25cm">'.$empleados[$i]->nameEmployee.'</p></td>';
                                            $empleados[$i]->id = 0;
                                            break;
                                        }
                                    }else if ($empleados[$i]->id != 0){
                                        $turnoEmpleado = false;
                                    }
                                }
                            }
                            if(!$turnoEmpleado && $j < $numTurno){
                                $tdemploy = $tdemploy.'<td class = "border"> </td>';
                            }
                        }
                        $tdemploy = $tdemploy.'</tr>';
                    }
                    $tdbody = $tdbody.$tdemploy;
                }   
                
                $table = $table.'<tr>'.$th.'</tr>'.'<tr>'.$tdworkshiftName.'</tr>'.'<tr>'.$tdworkshifts.'</tr>'.
                $tdbody.'</tbody></table></div>';
            }
            $html = $html.$table;
        }
        $html = '<div class = "container">'.$html.'</div>';
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'c',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 25,
            'margin_bottom' => 30,
            'margin_header' => 10,
            'margin_footer' => 10
        ]);

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;
        $mpdf->use_kwt = true;

        $stylesheet = file_get_contents('./mpdf/mpdfMycss.css');
        
        $mpdf->SetTitle($nombrePdf);
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLfooter($footer);
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($html,2);
        $mpdf->Output(storage_path('app/public/').$nombrePdf.'.pdf', \Mpdf\Output\Destination::FILE);
    }

    public function pdfOld($id,$typearea){

        $week = week::findOrFail($id);
        $codigo = DB::table('department_group')
                            ->where('id',$typearea)
                            ->get();
        $nombrePdf = 'RolTur'.$week->week_number.''.$week->year.''.$codigo[0]->code;
        $formateoIni = explode('-',$week->start_date);
        $fechaInicio = $formateoIni[2].'-'.$formateoIni[1].'-'.$formateoIni[0];
        $dias = array('','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo');
        $diasTitulo = array('','lunes','martes','miércoles','jueves','viernes','sábado','domingo');
        $meses = array('','ene.','feb.','mar.','abr.','may.','jun.','jul.','ago.','sep.','oct.','nov.','dic.');
        $fini = $diasTitulo[date('N', strtotime($fechaInicio))];
        $formateoFin = explode('-',$week->end_date);
        $fechaFin = $formateoFin[2].'-'.$formateoFin[1].'-'.$formateoFin[0];
        $fin = $diasTitulo[date('N', strtotime($fechaFin))];
        $nombreMes = $meses[date('n', strtotime($fechaFin))];
        PDF::SetTitle('Rol de Turnos');
        $renglones=2;
        PDF::AddPage();

        PDF::SetFont('helvetica','B',16);
        //PDF::Cell(50, 10, 'Rol de turnos del', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        //PDF::Cell(20,10,$fini,0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(10,10,$formateoIni[2],0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(8,10,'al',0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(20,10,$fin,0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(10,10,$formateoFin[2],0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(8,10,'de',0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(15,10,$nombreMes,0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(10,10,'de',0,false,'C',0,'',1,false,'M','M');
        //PDF::Cell(15,10,$formateoFin[0],0,false,'C',0,'',1,false,'M','M');

        $titulo = 'Rol de turnos del '.$fini.' '.$formateoIni[2].' al '.$fin.' '.$formateoFin[2].' de '.$nombreMes.' de '.$formateoFin[0];
        PDF::Cell(170,10, $titulo,0,false,'C',0,'',1,false,'M','M');
        $grupo = DB::table('departments')
                    ->where('dept_group_id',$typearea)
                    ->where('is_delete',0)
                    ->select('id AS id')
                    ->get();
        $Agrupo = [];
        for($x=0;count($grupo)>$x;$x++){
            $Agrupo[$x]=$grupo[$x]->id;
        }
        
        $departments = DB::table('week_department')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->where('week_id',$week->id)
                        ->whereIn('departments.id',$Agrupo)
                        ->select('departments.id AS idDepartment', 'departments.name AS nameDepartment','week_department.status AS status','week_department.group_id AS group')
                        ->get();
        $ejeX = 10;
        $ejeY = 25;
        $auxY = 0;
        $avance = 0;
        $tamañoCol = 0;
        for( $x = 0 ;count($departments)>$x;$x++){
            PDF::SetFont('helvetica','B',9);
            PDF::SetXY($ejeX,$ejeY);
            $avance = $ejeY;
            PDF::Cell(60,6,$departments[$x]->nameDepartment,1,false,'C',0,'',1,false,'M','M');
            PDF::Cell(8,6,'',0,false,'C',0,'',0,false,'M','M');
            //PDF::Ln();
            $avance+= 6 ;
            PDF::SetXY($ejeX,$avance);
            if($departments[$x]->status == 2){
                PDF::Cell(60,6,'Cerrado',1,false,'C',0,'',1,false,'M','M');
                $renglones = 3; 
            }else{
                $workshifts = DB::table('group_workshifts_lines')
                                ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                                ->where('group_workshifts_id',$departments[$x]->group)
                                ->where('is_delete','=','0')
                                ->orderBy('workshifts.order')
                                ->select('workshifts.id AS idWork', 'workshifts.name AS nameWork', 'workshifts.entry AS entry','workshifts.departure AS departure')
                                ->get();
                $job = DB::table('jobs')
                        ->where('jobs.department_id',$departments[$x]->idDepartment)
                        ->where('is_delete','=','0')
                        ->select('jobs.id AS idJob', 'jobs.name AS nameJob')
                        ->get();
                $numTurno = count($workshifts);
                $tamañoCol = 60/$numTurno;
                for( $y = 0 ; $numTurno > $y ; $y++ ){

                    PDF::Cell($tamañoCol,6,$workshifts[$y]->nameWork.' / '.substr($workshifts[$y]->entry, 0, -3).' '.substr($workshifts[$y]->departure, 0, -3),1,false,'C',0,'',1,false,'M','M');
                }
                $empleados = DB::table('week')
                                ->join('week_department','week.id','=','week_department.week_id')
                                ->join('departments','week_department.department_id','=','departments.id')
                                ->join('week_department_day','week_department.id','=','week_department_day.week_department_id')
                                ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                                ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                                ->join('employees','day_workshifts_employee.employee_id','=','employees.id')
                                ->where('week_id','=',$week->id)
                                ->select('day_workshifts_employee.job_id AS idJob','employees.name AS nameEmployee','employees.short_name AS shortName','day_workshifts.workshift_id AS id')
                                ->groupBy('employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id')
                                ->get();
                $renglones = 2;
                $avance+=6;
                $auxAvance = $avance;
                
                for( $z = 0 ; count($job) > $z ; $z++ ){
                    $ejeXAux = $ejeX;
                    
                    
                    //PDF::Ln();
                    PDF::SetXY($ejeXAux,$auxAvance);
                    PDF::SetFont('helvetica','B',9);
                    PDF::Cell(60,6,$job[$z]->nameJob,1,false,'C',0,'',1,false,'M','M');
                    //PDF::Ln();
                    
                    $auxAvance+=6;
                    PDF::SetXY($ejeXAux,$auxAvance);
                    $renglones++;
                    $contEmpleados=0;
                    $contadorRenglones=0;
                    $auxContRen = 0;
                    $numEmpleados = 0;
                    $numEmpleadosAnterior = 0;
                    for( $j = 0 ; $numTurno > $j ; $j++){
                        $numEmpleados = 0;
                        if($j!=0){
                        $auxAvance = $auxAvance - ($contEmpleados*6);}
                        $contEmpleados = 0;
                        for( $i = 0 ; count($empleados) > $i ; $i++){
                            if($job[$z]->idJob == $empleados[$i]->idJob && $workshifts[$j]->idWork == $empleados[$i]->id){
                                PDF::SetXY($ejeXAux,$auxAvance);
                                PDF::SetFont('helvetica','',8);
                                if($empleados[$i]->shortName != ''){
                                    PDF::Cell($tamañoCol,6,$empleados[$i]->shortName,1,false,'C',0,'',1,false,'M','M');
                                }else{
                                    PDF::Cell($tamañoCol,6,$empleados[$i]->nameEmployee,1,false,'C',0,'',1,false,'M','M');   
                                }
                                    
                                //$renglones++;
                                $auxAvance+=6;
                                $contEmpleados++;
                                $contadorRenglones++;
                                $numEmpleados++;
                            }
                        }
                        if($numEmpleados > $numEmpleadosAnterior){ 
                            $numEmpleadosAnterior = $numEmpleados;
                        }

                        if($contadorRenglones > $auxContRen ){$auxContRen = $contadorRenglones;}
                        $contadorRenglones=0;
                        $ejeXAux+=$tamañoCol;
                        
                    }
                    $renglones += $numEmpleadosAnterior;
                    $auxAvance = $auxAvance - ($contEmpleados*6);
                    $auxAvance = $auxAvance+($auxContRen*6);
                }   
            }
            $ejeX= $ejeX+68;
            if($auxY < $renglones){
                $auxY = $renglones;
            }            
            if($ejeX > 200){

                $ejeY = $ejeY + ($auxY*6) +6;
                $auxY = 0;
                $ejeX = 10;
            }
        }
        //$ejeY = $ejeY + ($auxY*5);
        $ejeY = 10;
        PDF::AddPage('L');
        PDF::setXY(10,$ejeY);
        $date1 = new DateTime($week->start_date);
        $date2 = new DateTime($week->end_date);
        $diff = $date1->diff($date2);
        $tamaño = $diff->days;
        $tamaño++;
        $tamañoDia = 260/$tamaño;
        $date = date($week->start_date);
        $auxX = 10;
        for($i = 0 ; $diff->days >= $i ; $i++){
            PDF::SetFont('helvetica','B',9);
            $fecha = $dias[date('N', strtotime($date))];
            PDF::Cell($tamañoDia,5,$fecha,1,false,'C',0,'',0,false,'M','M');
            PDF::setXY($auxX,$ejeY+5);
            $formateo = explode('-',$date);
            $formateoCalendario = $formateo[2].'-'.$formateo[1].'-'.$formateo[0];
            $nombreMes = $meses[date('n', strtotime($formateoCalendario))];
            $formateoCalImp = $formateo[2].'-'.$nombreMes;
            PDF::Cell($tamañoDia,5,$formateoCalImp,1,false,'C',0,'',0,false,'M','M');
            $auxX = $auxX + $tamañoDia;
            PDF::setXY($auxX,$ejeY);
            $date = date("Y-m-d",strtotime($date."+1 days"));
        }
        $ejeY = $ejeY + 10 ;
        $auxX = 10;
        $departments = DB::table('week_department')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->where('week_id',$week->id)
                        ->where('week_department.status',1)
                        ->select('departments.id AS idDepartment', 'departments.name AS nameDepartment','week_department.status AS status','week_department.group_id AS group')
                        ->get();
        $workshifts = DB::table('group_workshifts_lines')
                                ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                                ->where('group_workshifts_id',$departments[0]->group);
        for($x = 1 ; count($departments) > $x ; $x++){
            $workshifts = $workshifts->orWhere('group_workshifts_id',$departments[$x]->group);
        }
                                
        $workshifts = $workshifts->where('is_delete','=','0')
                                ->orderBy('group_workshifts_id')
                                ->orderBy('workshifts.order_view')
                                ->groupBy('workshifts.id','workshifts.name','workshifts.entry','workshifts.departure')
                                ->select('workshifts.id AS idWork', 'workshifts.name AS nameWork', 'workshifts.entry AS entry','workshifts.departure AS departure')
                                ->get();

        for($j = 0 ; count($workshifts) > $j ; $j++ ){
            PDF::setXY($auxX,$ejeY);
            PDF::SetFont('helvetica','B',9);
            PDF::Cell(260,5,$workshifts[$j]->nameWork.' de '.$workshifts[$j]->entry.' a '.$workshifts[$j]->departure,1,false,'C',0,'',0,false,'M','M');
            
            for($z = 0 ; count($departments) > $z ; $z++){
                $diasEmpleados = DB::table('week')
                    ->join('week_department','week.id','=','week_department.week_id')
                    ->join('departments','week_department.department_id','=','departments.id')
                    ->join('week_department_day','week_department.id','=','week_department_day.week_department_id')
                    ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                    ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                    ->join('employees','day_workshifts_employee.employee_id','=','employees.id')
                    ->where('week_id','=',$week->id)
                    ->where('day_workshifts.workshift_id','=',$workshifts[$j]->idWork)
                    ->where('departments.id','=',$departments[$z]->idDepartment)
                    ->select('day_workshifts_employee.job_id AS idJob','day_workshifts_employee.type_day_id AS tipo','employees.name AS nameEmployee','employees.short_name AS shortName','day_workshifts.workshift_id AS id')
                    ->orderBy('employee_id')
                    ->get();
                $numEmpleado = 0;
                if(count($diasEmpleados) != 0){
                    $ejeY= $ejeY + 5;
                    if($ejeY > 180){
                        $ejeY = 10;
                        PDF::AddPage('L');
                        PDF::setXY(10,$ejeY);    
                    }
                    PDF::setXY(10,$ejeY);
                    PDF::SetFont('helvetica','B',9);
                    PDF::Cell(260,5,$departments[$z]->nameDepartment,1,false,'C',0,'',0,false,'M','M');
                    while(count($diasEmpleados) > $numEmpleado ){
                        $ejeY= $ejeY + 5;
                        if($ejeY > 180){
                            $ejeY = 10;
                            PDF::AddPage('L');
                            PDF::setXY(10,$ejeY);    
                        }
                        for($x = 0 ; $diff->days >= $x ; $x++){
                            PDF::SetFont('helvetica','',9);
                             
                            PDF::setXY($auxX,$ejeY);
                            switch ($diasEmpleados[$numEmpleado]->tipo){
                                case 1 : 
                                    if($diasEmpleados[$numEmpleado]->shortName != ''){
                                        PDF::Cell($tamañoDia,5,$diasEmpleados[$numEmpleado]->shortName,1,false,'C',0,'',1,false,'M','M');
                                    }else{
                                        PDF::Cell($tamañoDia,5,$diasEmpleados[$numEmpleado]->nameEmployee,1,false,'C',0,'',1,false,'M','M');
                                    }
                                break;

                                case 2 :
                                    PDF::Cell($tamañoDia,5,'Incapacidad',1,false,'C',0,'',1,false,'M','M');
                                break;

                                case 3 :
                                    PDF::Cell($tamañoDia,5,'Vacaciones',1,false,'C',0,'',1,false,'M','M');
                                break;

                                case 4 :
                                    PDF::Cell($tamañoDia,5,'Días Festivos',1,false,'C',0,'',1,false,'M','M');
                                break;

                                case 5 :
                                    PDF::Cell($tamañoDia,5,'Descanso',1,false,'C',0,'',1,false,'M','M');
                                break;
                            }
                            $numEmpleado++;
                            $auxX = $auxX+$tamañoDia;
                            
                        }
                        
                        $auxX = 10;
                    }

                    if($ejeY > 180){
                        $ejeY = 10;
                        PDF::AddPage('L');
                        PDF::setXY(10,$ejeY);    
                    }
                }
                  
            }
            $ejeY= $ejeY + 5; 
        }
        
        PDF::Output(storage_path('app/public/').$nombrePdf.'.pdf', 'F');
        




    }

    public function copyRol(Request $request){
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', '1')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','employees.name AS nameEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        $info = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->join('week_department_day','week_department.id','=','week_department_day.week_department_id')
                        ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                        ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('employees','day_workshifts_employee.employee_id','=','employees.id')
                        ->join('jobs','day_workshifts_employee.job_id','=','jobs.id')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('week_id','=',$request->semana)
                        ->select('day_workshifts_employee.job_id AS idJob','employees.id AS idEmployee','day_workshifts.workshift_id AS id','departments.id AS idD')
                        ->groupBy('employees.id','employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id','departments.id')
                        ->orderBy('employee_id')
                        ->get();
        $departments = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('week_id','=',$request->semana)
                        ->select('departments.id AS idDepart','departments.name AS nameDepart','week_department.group_id AS group','week_department.status AS status')
                        ->get();
        $workshifts = DB::table('group_workshifts')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift')
                        ->get();
        $horarios = DB::table('group_workshifts')
                        ->join('group_workshifts_lines','group_workshifts_lines.group_workshifts_id','=','group_workshifts.id')
                        ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift','workshifts.name AS nameWork','workshifts.id AS idWork')
                        ->orderBy('group_workshifts.id','desc')
                        ->orderBy('workshifts.id','desc')
                        ->get();
        $jobs = DB::table('jobs')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('jobs.id')
                        ->where('jobs.is_delete','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','departments.id AS idDepartment','departments.name AS nameDepartment')
                        ->get();
        
        
        return response()->json(array($employees,$info,$departments,$workshifts,$horarios,$jobs));

    }
    public function rotRol(Request $request){
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', '1')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','employees.name AS nameEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        $info = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->join('week_department_day','week_department.id','=','week_department_day.week_department_id')
                        ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                        ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('employees','day_workshifts_employee.employee_id','=','employees.id')
                        ->join('jobs','day_workshifts_employee.job_id','=','jobs.id')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->where('week_id','=',$request->semana)
                        ->select('day_workshifts_employee.job_id AS idJob','employees.id AS idEmployee','day_workshifts.workshift_id AS id','departments.id AS idD')
                        ->groupBy('employees.id','employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id','departments.id')
                        ->orderBy('employee_id')
                        ->get();
        $departments = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->where('week_id','=',$request->semana)
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('departments.id AS idDepart','departments.name AS nameDepart','week_department.group_id AS group','week_department.status AS status')
                        ->get();
        $workshifts = DB::table('group_workshifts')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift')
                        ->get();
        $horarios = DB::table('group_workshifts')
                        ->join('group_workshifts_lines','group_workshifts_lines.group_workshifts_id','=','group_workshifts.id')
                        ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift','workshifts.name AS nameWork','workshifts.id AS idWork', 'workshifts.order AS rotate')
                        ->orderBy('group_workshifts.id','desc')
                        ->orderBy('workshifts.id','desc')
                        ->get();
        $jobs = DB::table('jobs')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('jobs.id')
                        ->where('jobs.is_delete','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','departments.id AS idDepartment','departments.name AS nameDepartment')
                        ->get();
        
        
        return response()->json(array($employees,$info,$departments,$workshifts,$horarios,$jobs));

    }

    public function editRol(Request $request){
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', '1')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','employees.name AS nameEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        $info = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->join('week_department_day','week_department.id','=','week_department_day.week_department_id')
                        ->join('day_workshifts','week_department_day.id','=','day_workshifts.day_id')
                        ->join('day_workshifts_employee','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('employees','day_workshifts_employee.employee_id','=','employees.id')
                        ->join('jobs','day_workshifts_employee.job_id','=','jobs.id')
                        ->where('week_id','=',$request->semana)
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('day_workshifts_employee.job_id AS idJob','employees.id AS idEmployee','day_workshifts.workshift_id AS id','departments.id AS idD')
                        ->groupBy('employees.id','employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id','departments.id')
                        ->orderBy('employee_id')
                        ->get();
        $departments = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->where('week_id','=',$request->semana)
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('departments.id AS idDepart','departments.name AS nameDepart','week_department.group_id AS group','week_department.status AS status')
                        ->get();
        $workshifts = DB::table('group_workshifts')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift')
                        ->get();
        $horarios = DB::table('group_workshifts')
                        ->join('group_workshifts_lines','group_workshifts_lines.group_workshifts_id','=','group_workshifts.id')
                        ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift','workshifts.name AS nameWork','workshifts.id AS idWork')
                        ->orderBy('group_workshifts.id','desc')
                        ->orderBy('workshifts.id','desc')
                        ->get();
        $jobs = DB::table('jobs')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('jobs.id')
                        ->where('jobs.is_delete','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','departments.id AS idDepartment','departments.name AS nameDepartment')
                        ->get();
        $week = DB::table('week')
                        ->where('week.is_delete','0')
                        ->where('week.id',$request->semana)
                        ->select('week.start_date AS start','week.end_date AS end','week.id AS id')
                        ->get();
        
        
        return response()->json(array($employees,$info,$departments,$workshifts,$horarios,$jobs,$week));

    }
    
}




