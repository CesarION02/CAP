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
    {   $typeArea = $id;
        $week = DB::table('pdf_week')
                ->join('week','week.id','=','pdf_week.week_id')
                ->select('week.start_date AS start','week.end_date AS end','week.id AS id')
                ->get();
        $newest = week::where('is_delete','=',0)->orderBy('updated_at')->first();
        return view('shiftprogramming.index', compact('typeArea'),compact('newest'))->with('week',$week);

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

    public function newShift(Request $request){
        $departments = DB::table('jobs')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('departments.id')
                        ->orderBy('jobs.id')
                        ->where('jobs.is_delete','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','departments.id AS idDepartment','departments.name AS nameDepartment')
                        ->get();
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('departments.dept_group_id',$request->typearea)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob','employees.name AS nameEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        $group_workshift = DB::table('group_workshifts')
                        ->where('group_workshifts.is_delete','0')
                        ->select('group_workshifts.id AS idShift','group_workshifts.name AS nameShift')
                        ->get();
        return response()->json(array($employees,$departments,$group_workshift));

    }

    public function workShift(Request $request){
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.name')
                        ->where('employees.is_delete','0')
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
                        return response()->json($workshifts);
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
                                            $day_workshifts_employee->is_rest = $request->arrCalendarioDias[$contEmpl][$j];
                                            $day_workshifts_employee->is_delete = 0; 
                                            $day_workshifts_employee->save(); 
                                        }
                                    }
                                }
                             $flagPDF = 1;      
                            }
                        }


                    }
                }
               
                //$mod_date = strtotime($date."+ 2 days");
            }
        }
        if($flagPDF == 1){
            $this->pdf($week->id);
            $nombrePdf = 'RolTur'.$week->week_number.''.$week->year.'.pdf';
            if($request->pdfFlag == 0){
                $guardarPdf = new pdf_week();
            }else{
                $guardarPdf = pdf_week::where('week_id', $week->id)->get();
            }
            
            $guardarPdf->week_id = $week->id;
            $guardarPdf->url = $nombrePdf;
            $guardarPdf->is_delete = 0;
            $guardarPdf->save();
            
        }
        return response()->json(array($data,$nombrePdf));

    }

    public function pdf($id){

        $week = week::findOrFail($id);
        $nombrePdf = 'RolTur'.$week->week_number.''.$week->year;
        $formateoIni = explode('-',$week->start_date);
        $fechaInicio = $formateoIni[2].'-'.$formateoIni[1].'-'.$formateoIni[0];
        $dias = array('','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
        $meses = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
        $fini = $dias[date('N', strtotime($fechaInicio))];
        $formateoFin = explode('-',$week->end_date);
        $fechaFin = $formateoFin[2].'-'.$formateoFin[1].'-'.$formateoFin[0];
        $fin = $dias[date('N', strtotime($fechaFin))];
        $nombreMes = $meses[date('n', strtotime($fechaFin))];
        PDF::SetTitle('Rol de Turnos');
        $renglones=2;
        PDF::AddPage();

        PDF::SetFont('helvetica','B',16);
        PDF::Cell(55, 10, 'Rol de turnos del', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        PDF::Cell(25,10,$fini,0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(10,10,$formateoIni[2],0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(10,10,'al',0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(25,10,$fin,0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(10,10,$formateoFin[2],0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(10,10,'de',0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(20,10,$nombreMes,0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(10,10,'de',0,false,'C',0,'',1,false,'M','M');
        PDF::Cell(15,10,$formateoFin[0],0,false,'C',0,'',1,false,'M','M');
        
        $departments = DB::table('week_department')
                        ->join('departments','week_department.department_id','=','departments.id')
                        ->where('week_id',$week->id)
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
                    PDF::Cell($tamañoCol,6,$workshifts[$y]->nameWork,1,false,'C',0,'',1,false,'M','M');
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
                    for( $j = 0 ; $numTurno > $j ; $j++){
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
                                    
                                $renglones++;
                                $auxAvance+=6;
                                $contEmpleados++;
                                $contadorRenglones++;
                            }
                        }
                        if($contadorRenglones > $auxContRen ){$auxContRen = $contadorRenglones;}
                        $contadorRenglones=0;
                        $ejeXAux+=$tamañoCol;
                        
                    }
                    $auxAvance = $auxAvance - ($contEmpleados*6);
                    $auxAvance = $auxAvance+($auxContRen*6);
                }   
            }
            $ejeX= $ejeX+68;
            if($auxY < $renglones){
                $auxY = $renglones;
            }            
            if($ejeX > 200){

                $ejeY = $ejeY + ($auxY*5);
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
                    ->select('day_workshifts_employee.job_id AS idJob','day_workshifts_employee.is_rest AS rest','employees.name AS nameEmployee','employees.short_name AS shortName','day_workshifts.workshift_id AS id')
                    ->orderBy('employee_id')
                    ->get();
                $numEmpleado = 0;
                if(count($diasEmpleados) != 0){
                    $ejeY= $ejeY + 5;
                    PDF::setXY(10,$ejeY);
                    PDF::SetFont('helvetica','B',9);
                    PDF::Cell(260,5,$departments[$z]->nameDepartment,1,false,'C',0,'',0,false,'M','M');
                    while(count($diasEmpleados) > $numEmpleado ){
                        $ejeY= $ejeY + 5;
                        for($x = 0 ; $diff->days >= $x ; $x++){
                            PDF::SetFont('helvetica','',9);
                             
                            PDF::setXY($auxX,$ejeY);
                            if($diasEmpleados[$numEmpleado]->rest > 0){
                                PDF::Cell($tamañoDia,5,'Descanso',1,false,'C',0,'',1,false,'M','M');
                            }else{
                                if($diasEmpleados[$numEmpleado]->shortName != ''){
                                    PDF::Cell($tamañoDia,5,$diasEmpleados[$numEmpleado]->shortName,1,false,'C',0,'',1,false,'M','M');
                                }else{
                                    PDF::Cell($tamañoDia,5,$diasEmpleados[$numEmpleado]->nameEmployee,1,false,'C',0,'',1,false,'M','M');
                                }
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
                        ->select('day_workshifts_employee.job_id AS idJob','employees.id AS idEmployee','day_workshifts.workshift_id AS id','departments.id AS idD')
                        ->groupBy('employees.id','employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id','departments.id')
                        ->orderBy('employee_id')
                        ->get();
        $departments = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
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
                        ->select('day_workshifts_employee.job_id AS idJob','employees.id AS idEmployee','day_workshifts.workshift_id AS id','departments.id AS idD')
                        ->groupBy('employees.id','employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id','departments.id')
                        ->orderBy('employee_id')
                        ->get();
        $departments = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
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
                        ->select('day_workshifts_employee.job_id AS idJob','employees.id AS idEmployee','day_workshifts.workshift_id AS id','departments.id AS idD')
                        ->groupBy('employees.id','employee_id','day_workshifts_employee.job_id','employees.name','day_workshifts.workshift_id','departments.id')
                        ->orderBy('employee_id')
                        ->get();
        $departments = DB::table('week')
                        ->join('week_department','week.id','=','week_department.week_id')
                        ->join('departments','week_department.department_id','=','departments.id')
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
        $week = DB::table('week')
                        ->where('week.is_delete','0')
                        ->where('week.id',$request->semana)
                        ->select('week.start_date AS start','week.end_date AS end')
                        ->get();
        
        
        return response()->json(array($employees,$info,$departments,$workshifts,$horarios,$jobs,$week));

    }
    
}




