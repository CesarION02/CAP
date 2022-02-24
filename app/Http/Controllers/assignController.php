<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Models\assign_schedule;
use App\Models\schedule_template;
use App\Models\schedule_day;
use App\Models\department;
use App\Models\employees;
use App\Models\group_assign;
use App\Models\holidayAux;
use App\Models\holiday;
use App\SUtils\SDateTimeUtils;
use App\Models\groupSchedule;
use App\SUtils\SDelayReportUtils;
use DateTime;
use DB;
use DatePeriod;
use DateInterval;

class assignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        
        $datas = DB::select("SELECT a.fecha_inicio, a.fecha_fin, b.name AS nombreEmpleado, s.name AS nombreHorario, a.group_assign_id, a.id
        FROM (
            SELECT start_date as fecha_inicio, end_date as fecha_fin ,p.employee_id, schedule_template_id, group_assign_id,id FROM schedule_assign p WHERE is_delete = 0 AND schedule_template_id != ".$iTemplateId." ORDER BY p.employee_id, start_date DESC
        ) a
        INNER JOIN employees b ON a.employee_id = b.id
        INNER JOIN schedule_template s ON a.schedule_template_id = s.id
        GROUP By a.employee_id");

        $datasDept = DB::select("SELECT a.fecha_inicio, a.fecha_fin, b.name AS nombreEmpleado, s.name AS nombreHorario, a.group_assign_id, a.id
        FROM (
            SELECT start_date as fecha_inicio, end_date as fecha_fin ,p.employee_id, schedule_template_id, group_assign_id,id FROM schedule_assign p WHERE is_delete = 0 AND schedule_template_id != ".$iTemplateId." ORDER BY p.employee_id, start_date DESC
        ) a
        INNER JOIN employees b ON a.employee_id = b.id
        INNER JOIN departments c ON b.department_id = c.id
        INNER JOIN schedule_template s ON a.schedule_template_id = s.id
        GROUP By b.department_id");


        return view('assign.index', compact('datas'))->with('datasD',$datasDept);    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tipo)
    {
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        $employee = employees::where('is_delete','0')->where('is_active', true)->orderBy('name','ASC')->pluck('id','name');
        $department = department::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $schedule_template = schedule_template::where('is_delete','0')->where('id','!=',$iTemplateId)->orderBy('name','ASC')->pluck('id','name');

        return view('assign.create')->with('employee',$employee)->with('department',$department)->with('schedule_template',$schedule_template)->with('flag',0)->with('tipo',$tipo);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $start = null;
        $end = null;
        if($request->start_date != ''){
            $start = $request->start_date;
            $end = $request->end_date;
        }
        if($request->tipo == 1){
            $empleados = $request->empleado;
            if(count($empleados) == 1){
                $asignacion = new assign_schedule();
                $asignacion->employee_id = $empleados[0];
                $asignacion->schedule_template_id = $request->horario;
                $asignacion->start_date = $start;
                $asignacion->end_date = $end;
                $asignacion->created_by = session()->get('user_id');
                $asignacion->updated_by = session()->get('user_id');
                $asignacion->save();
    
            }else{
                $grupo = new group_assign();
                $grupo->created_by = session()->get('user_id');
                $grupo->updated_by = session()->get('user_id');
                $grupo->save();
    
                for($y = 0 ; count($empleados) > $y ; $y++){
                    $asignacion = new assign_schedule();
                    $asignacion->employee_id = $empleados[$y];
                    $asignacion->start_date = $start;
                    $asignacion->end_date = $end;
                    $asignacion->schedule_template_id = $request->horario;
                    $asignacion->group_assign_id = $grupo->id;
                    $asignacion->created_by = 1;
                    $asignacion->updated_by = 1;
                    $asignacion->save();
                }
    
            }
        }else{
            $asignacion = new assign_schedule();
            $asignacion->department_id = $request->departamento;
            $asignacion->schedule_template_id = $request->horario;
            $asignacion->start_date = $start;
            $asignacion->end_date = $end;
            $asignacion->created_by = session()->get('user_id');
            $asignacion->updated_by = session()->get('user_id');
            $asignacion->save();   
        }
        
        return redirect('assign')->with('mensaje','Asignación fue creada con éxito');   
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
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        $employee = employees::where('is_delete','0')->where('is_active', true)->orderBy('name','ASC')->pluck('id','name');
        $department = department::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $schedule_template = schedule_template::where('is_delete','0')->where('id','!=',$iTemplateId)->orderBy('name','ASC')->pluck('id','name');
        $datas = assign_schedule::find($id);
        $auxiliar = 0;
        $empleados = 0;
        if($datas->employee_id == 0){
            $tipo = 2;
        }else{
            $tipo = 1;
        }
        $flag = 0;
        if($datas->group_assign_id != null){
            $auxiliar = $datas->group_assign_id;
            $empleados = DB::table('schedule_assign')
                        ->join('group_assign','schedule_assign.group_assign_id','=','group_assign.id')
                        ->where('group_assign_id',$auxiliar)
                        ->select('employee_id AS idEmp','schedule_template_id AS idTemplate','department_id AS idDepartment','schedule_assign.id AS id')
                        ->get();
            $flag = 1;
        }

        return view('assign.edit', compact('datas'))->with('schedule_template',$schedule_template)->with('department',$department)->with('employee',$employee)->with('flag',$flag)->with('tipo',$tipo)->with('empleados',$empleados)->with('auxiliar',$auxiliar);
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
        $start = null;
        $end = null;
        if($request->start_date != ''){
            $start = $request->start_date;
            $end = $request->end_date;
        }
        if($request->tipo == 1){
            $empleados = $request->empleado;
            if($request->group == 0){
                assign_schedule::where('id', $id)->delete();
                if(count($empleados) == 1){
                    $asignacion = new assign_schedule();
                    $asignacion->employee_id = $empleados[0];
                    $asignacion->schedule_template_id = $request->horario;
                    $asignacion->start_date = $start;
                    $asignacion->end_date = $end;
                    $asignacion->created_by = session()->get('user_id');
                    $asignacion->updated_by = session()->get('user_id');
                    $asignacion->save();    
                }else{
                    $grupo = new group_assign();
                    $grupo->created_by = session()->get('user_id');
                    $grupo->updated_by = session()->get('user_id');
                    $grupo->save();
    
                    for($y = 0 ; count($empleados) > $y ; $y++){
                        $asignacion = new assign_schedule();
                        $asignacion->employee_id = $empleados[$y];
                        $asignacion->start_date = $start;
                        $asignacion->end_date = $end;
                        $asignacion->schedule_template_id = $request->horario;
                        $asignacion->group_assign_id = $grupo->id;
                        $asignacion->created_by = session()->get('user_id');
                        $asignacion->updated_by = session()->get('user_id');
                        $asignacion->save();
                    }
                }
            }else{
                assign_schedule::where('group_assign_id', $request->group)->delete();
                if(count($empleados) == 1){
                    $group_assign = group_assign::find($request->group);
                    $group_assign->is_delete = 1;
                    $group_assign->save();
                    $asignacion = new assign_schedule();
                    $asignacion->employee_id = $empleados[0];
                    $asignacion->schedule_template_id = $request->horario;
                    $asignacion->start_date = $start;
                    $asignacion->end_date = $end;
                    $asignacion->created_by = session()->get('user_id');
                    $asignacion->updated_by = session()->get('user_id');
                    $asignacion->save();    
                }else{
                    for($y = 0 ; count($empleados) > $y ; $y++){
                        $asignacion = new assign_schedule();
                        $asignacion->employee_id = $empleados[$y];
                        $asignacion->start_date = $start;
                        $asignacion->end_date = $end;
                        $asignacion->schedule_template_id = $request->horario;
                        $asignacion->group_assign_id = $request->group;
                        $asignacion->created_by = session()->get('user_id');
                        $asignacion->updated_by = session()->get('user_id');
                        $asignacion->save();
                    }      
                }
            }
        }else{
            $assign = assign_schedule::find($id);
            $assign->department_id = $request->departamento;
            $assign->schedule_template_id = $request->horario;
            $assign->start_date = $start;
            $assign->end_date = $end;
            $assign->updated_by = session()->get('user_id');
            $assign->save();       
        } 
        
        return redirect('assign')->with('mensaje','Asignación fue actualizada con éxito');  
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
            $datas = assign_schedule::find($id);
            $auxiliar = $datas->group_assign_id;
            if($auxiliar != null){
                $empleados = DB::table('schedule_assign')
                        ->join('group_assign','schedule_assign.group_assign_id','=','group_assign.id')
                        ->where('group_assign_id',$auxiliar)
                        ->select('schedule_assign.id AS id')
                        ->get();
                for($i = 0 ; count($empleados) > $i ; $i++){
                    $datas = assign_schedule::find($empleados[$i]->id);
                    $datas->is_delete = 1;
                    $datas->save();    
                }
            }else{
                $datas->is_delete = 1;
                $datas->save();
            }
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }


    public function indexOneDay(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($startDate == null) {
            $now = Carbon::now();
            $month = $now->format('m');
            $year = $now->format('Y');
            $startDate = SDateTimeUtils::getFirstDayOfMonth($month, $year, 'Y-m-d');
            $endDate = SDateTimeUtils::getLastDayOfMonth($month, $year, 'Y-m-d');
        }

        $lSchedules = $this->getData($startDate, $endDate);

        $lEmployees = employees::where('is_delete', false)
                                ->where('is_active', true)
                                ->select('id', 'num_employee', 'name')
                                ->orderBy('name', 'ASC')
                                ->orderBy('num_employee', 'ASC')
                                ->get();

        $iTemplateId = env('TMPLTE_SATURDAYS', 1);
        $iGrpSchId = env('GRP_SCHDLS_SATUDY', 1);

        $holidays = holiday::select('id',
                                'name',
                                'fecha',
                                'year',
                                'is_delete')
                            ->where('is_delete', false)
                            ->get();

        $holidays[] = new holiday();

        return view('scheduleone.index')->with('lSchedules', $lSchedules)
                                        ->with('lEmployees', $lEmployees)
                                        ->with('holidays', $holidays)
                                        ->with('startDate', $startDate)
                                        ->with('endDate', $endDate)
                                        ->with('iTemplateId', $iTemplateId)
                                        ->with('iGrpSchId', $iGrpSchId);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function storeOne(Request $request)
    {
        /**
         * -1: cancelar
         * 0: insertar guardia
         * 1: remplazar guardia
         * 2: desplazar guardias
         */
        $iAction = $request->i_action;

        $resp = null;
        switch ($iAction) {
            case 2: 
                $toReplace = json_decode($request->to_change);
                $oAssign = json_decode($request->ass_objs);
                $lAssigns = assign_schedule::where('schedule_template_id', env('TMPLTE_SATURDAYS', 1))
                                ->where('group_schedules_id', env('GRP_SCHDLS_SATUDY', 1))
                                ->where('is_delete', false)
                                ->where('start_date', '>=', $toReplace->start_date)
                                ->orderBy('start_date', 'ASC')
                                ->orderBy('employee_id', 'DESC')
                                ->get();

                for ($i = 0; $i < sizeof($lAssigns); $i++) {
                    $oNew = null;
                    if ($i == (sizeof($lAssigns) - 1)) {
                        $oPrevious = $lAssigns[$i];

                        $newDate = date('Y-m-d', strtotime($oPrevious->start_date. ' + 7 days'));

                        $oNew = new assign_schedule();

                        $oNew->employee_id = $oPrevious->employee_id;
                        $oNew->group_assign_id = null;
                        $oNew->schedule_template_id = env('TMPLTE_SATURDAYS', 1);
                        $oNew->group_schedules_id = env('GRP_SCHDLS_SATUDY', 1);
                        $oNew->start_date = $newDate;
                        $oNew->end_date = $newDate;
                        $oNew->is_delete = false;
                        $oNew->order_gs = 0;
                        $oNew->created_by = session()->get('user_id');
                        $oNew->updated_by = session()->get('user_id');

                        $oNew->save();
                    }

                    if ($i == 0) {
                        $oNewAss = clone $lAssigns[$i];

                        $oNewAss->employee_id = $oAssign->employee_id;
                        $oNewAss->start_date = $oAssign->start_date;
                        $oNewAss->end_date = $oAssign->end_date;

                        $oNewAss->save();
                    }

                    if ($i > 0 && $i < (sizeof($lAssigns))) {
                        $oPrevious = $lAssigns[$i - 1];
                        $oNew = clone $lAssigns[$i];

                        $oNew->employee_id = $oPrevious->employee_id;
                        // $oNew->start_date = $oPrevious->start_date;
                        // $oNew->end_date = $oPrevious->end_date;

                        $oNew->save();
                    }
                }

                break;

            case 1:
                $toReplace = json_decode($request->to_change);
                $oToReplace = assign_schedule::find($toReplace->id);
                $oToReplace->is_delete = true;
                $oToReplace->updated_by = session()->get('user_id');
                $oToReplace->save();

            case 0:
                $oAssign = json_decode($request->ass_objs);

                $obj = new assign_schedule();

                $obj->employee_id = $oAssign->employee_id;
                $obj->group_assign_id = null;
                $obj->schedule_template_id = env('TMPLTE_SATURDAYS', 1);
                $obj->start_date = $oAssign->start_date;
                $obj->end_date = $oAssign->start_date;
                $obj->group_schedules_id = env('GRP_SCHDLS_SATUDY', 1);
                $obj->order_gs = 0;
                $obj->is_delete = false;
                $obj->created_by = session()->get('user_id');
                $obj->updated_by = session()->get('user_id');

                $resp = $obj->save();
                break;
            
            default:
                # code...
                break;
        }

        // $lSchedules = $this->getData();
        $lSchedules = $resp;

        return json_encode($lSchedules);
    }

    public function updateOne(Request $request, $id)
    {
        $oAssing = assign_schedule::find($id);

        if ($oAssing == null) {
            return;
        }

        $oAssing->employee_id = $request->emply_id;
        $oAssing->updated_by = session()->get('user_id');
        $oAssing->save();

        $lSchedules = $this->getData();

        return json_encode($lSchedules);
    }

    public function deleteOne(Request $request, $id)
    {
        $oAssing = assign_schedule::find($id);

        if ($oAssing == null) {
            return;
        }

        $oAssing->is_delete = true;
        $oAssing->updated_by = session()->get('user_id');
        $oAssing->save();

        // $lSchedules = $this->getData();
        $lSchedules = $oAssing;

        return json_encode($lSchedules);
    }

    
    public function getData($startDate = null, $endDate = null)
    {
        $data = DB::table('schedule_assign AS sa')
                            ->join('employees AS e', 'e.id', '=', 'sa.employee_id')
                            ->leftjoin('group_schedule AS gs', 'gs.id', '=', 'sa.group_schedules_id')
                            ->select('e.name', 
                                        'e.num_employee', 
                                        'sa.start_date', 
                                        'sa.end_date', 
                                        'sa.group_schedules_id', 
                                        'sa.order_gs',
                                        'sa.employee_id',
                                        'sa.id'
                                    );
        if ($startDate != null && $endDate != null) {
            $data = $data->whereBetween('sa.start_date', [$startDate, $endDate]);
        }

        $data = $data->where('sa.is_delete', false)
                            ->where('schedule_template_id', env('TMPLTE_SATURDAYS', 1))
                            ->where('group_schedules_id', env('GRP_SCHDLS_SATUDY', 1))
                            ->orderBy('start_date', 'ASC')
                            //->orderBy('order_gs', 'ASC')
                            ->get();

        $holAuxs = DB::table('holidays_aux')
                        ->selectRaw('"FESTIVO" AS name,
                            "" AS num_employee,
                            text_description,
                            dt_date AS start_date,
                            dt_date AS end_date,
                            "0" AS employee_id,
                            id AS h_id,
                            holiday_id,
                            0 AS id')
                        ->where('is_delete', false);

        if ($startDate != null && $endDate != null) {
            $holAuxs = $holAuxs->whereBetween('dt_date', [$startDate, $endDate]);
        }

        $holAuxs = $holAuxs->get();

        foreach ($holAuxs as $ha) {
            $data[] = $ha;
        }

        return $data;
    }

    public function eliminar(Request $request,$id)
    {
        if ($request->ajax()) {
            $datas = assign_schedule::find($id);
            $auxiliar = $datas->group_schedules_id;
            if($auxiliar != null){
                $empleados = DB::table('schedule_assign')
                        ->where('group_schedules_id',$auxiliar)
                        ->select('schedule_assign.id AS id')
                        ->get();
                for($i = 0 ; count($empleados) > $i ; $i++){
                    $datas = assign_schedule::find($empleados[$i]->id);
                    $datas->is_delete = 1;
                    $datas->save();    
                }
            }else{
                $datas->is_delete = 1;
                $datas->save();
            }
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }   
    }

    public function programming($id){
        if (session()->get('rol_id') != 1){
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$id)
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
                    ->select('employees.id AS id','employees.name AS name', 'department_group.name AS nameGroup')
                    ->get();
        }else{
            $employee = DB::table('employees')
                    ->join('jobs','jobs.id','=','employees.job_id')
                    ->join('departments','departments.id','=','employees.department_id')
                    ->join('department_group','department_group.id','=','departments.dept_group_id')
                    ->where('employees.is_active',1)
                    ->orderBy('employees.name')
                    ->select('employees.id AS id','employees.name AS name', 'department_group.name AS nameGroup')
                    ->get();    
        }
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        $schedule_template = schedule_template::where('is_delete','0')->where('id','!=',$iTemplateId)->orderBy('name','ASC')->pluck('id','name');

        return view('assign.programming')->with('employees',$employee)->with('schedule_template',$schedule_template)->with('idGroup',$id);   
    }

    public function dayProgramming($id){
        if (session()->get('rol_id') != 1){
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$id)
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
                    ->select('employees.id AS id','employees.name AS name', 'department_group.name AS nameGroup')
                    ->get();
        }else{
            $employee = DB::table('employees')
                    ->join('jobs','jobs.id','=','employees.job_id')
                    ->join('departments','departments.id','=','employees.department_id')
                    ->join('department_group','department_group.id','=','departments.dept_group_id')
                    ->where('employees.is_active',1)
                    ->orderBy('employees.name')
                    ->select('employees.id AS id','employees.name AS name', 'department_group.name AS nameGroup')
                    ->get();    
        }
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        $schedule_template = schedule_template::where('is_delete','0')->where('id','=',$iTemplateId)->orderBy('name','ASC')->pluck('id','name');

        return view('assign.dayProgramming')->with('employees',$employee)->with('schedule_template',$schedule_template)->with('idGroup',$id);   
    }

    public function schedule_template(){
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        $schedule_template = DB::table('schedule_template')
                ->where('is_delete','0')
                ->where('id','!=',$iTemplateId)
                ->orderBy('name')
                ->select('id AS id','name AS name')
                ->get();
        return response()->json($schedule_template);
    }

    public function guardar(Request $request){
        $start = null;
        $end = null;
        $orden = null;
        $group_num = null;

        if($request->start_date != ''){
            $start = $request->start_date;
            $end = $request->end_date;
        }
        
        if($request->contador > 1){
            $group = new groupSchedule();
            $group->name = $request->nameGroup;
            $group->delete = false;
            $group->created_by = session()->get('user_id');
            $group->updated_by = session()->get('user_id');
            $group->save();
            $group_num = $group->id;
        }
        for($i = 1 ; $request->contador >= $i ; $i++){
            
            $asignacion = new assign_schedule();
            $asignacion->employee_id = $request->empleado;
            $cadena = 'horario'.$i;
            $orden = 'orden'.$i;
            $asignacion->schedule_template_id = $request->$cadena;
            $asignacion->start_date = $start;
            $asignacion->end_date = $end;
            $asignacion->order_gs = $request->$orden;
            $asignacion->group_schedules_id = $group_num;
            $asignacion->created_by = session()->get('user_id');
            $asignacion->updated_by = session()->get('user_id');
            $asignacion->save();
        } 
        $url =  'assigns/viewProgramming';
        
        return redirect($url)->with('mensaje','Asignación fue creada con éxito');
    }

    public function guardarDayprogram(Request $request){
        $start = null;
        $end = null;
        $orden = null;
        $group_num = null;

        if($request->start_date != ''){
            $start = $request->start_date;
            $end = $request->end_date;
        }
            
        $asignacion = new assign_schedule();
        $asignacion->employee_id = $request->empleado;
        $asignacion->schedule_template_id = $request->horario;
        $asignacion->start_date = $start;
        $asignacion->end_date = $start;
        $asignacion->order_gs = 1;
        $asignacion->created_by = session()->get('user_id');
        $asignacion->updated_by = session()->get('user_id');
        $asignacion->save();
         
        $url =  'assigns/viewDayProgram';
        
        return redirect($url)->with('mensaje','Asignación fue creada con éxito');
    }

    public function editProgramming ($id){
        $grupo = assign_schedule::find($id);
        $auxiliar = $grupo->group_schedules_id;
        if($auxiliar != null){
            $assigns = DB::table('schedule_assign')
                ->join('employees','employees.id','=','schedule_assign.employee_id')
                ->join('schedule_template','schedule_template.id','=','schedule_assign.schedule_template_id')
                ->join('schedule_day','schedule_day.schedule_template_id','=','schedule_template.id')
                ->where('schedule_assign.group_schedules_id',$auxiliar)
                ->select('employees.id AS id','employees.name AS name','schedule_assign.start_date AS startDate','schedule_assign.end_date AS endDate','schedule_day.day_name AS dayName','schedule_day.entry AS entry','schedule_day.departure AS departure','schedule_template.name AS templateName','schedule_assign.order_gs AS orden','schedule_assign.id AS idAssign')
                ->orderBy('employees.id')
                ->orderBy('schedule_assign.order_gs')
                ->orderBy('schedule_day.day_num')
                ->get();
        }else{
            $assigns = DB::table('schedule_assign')
                ->join('employees','employees.id','=','schedule_assign.employee_id')
                ->join('schedule_template','schedule_template.id','=','schedule_assign.schedule_template_id')
                ->join('schedule_day','schedule_day.schedule_template_id','=','schedule_template.id')
                ->where('schedule_assign.id',$id)
                ->select('employees.id AS id','employees.name AS name','schedule_assign.start_date AS startDate','schedule_assign.end_date AS endDate','schedule_day.day_name AS dayName','schedule_day.entry AS entry','schedule_day.departure AS departure','schedule_template.name AS templateName','schedule_assign.order_gs AS orden','schedule_assign.id AS idAssign')
                ->orderBy('employees.id')
                ->orderBy('schedule_assign.order_gs')
                ->orderBy('schedule_day.day_num')
                ->get(); 
        }
        

        return view('assign.editprogramming')->with('assigns',$assigns);       

    }

    public function editDayProgramm ($id){
        $grupo = assign_schedule::find($id);
        $assigns = DB::table('schedule_assign')
                ->join('employees','employees.id','=','schedule_assign.employee_id')
                ->join('schedule_template','schedule_template.id','=','schedule_assign.schedule_template_id')
                ->join('schedule_day','schedule_day.schedule_template_id','=','schedule_template.id')
                ->where('schedule_assign.id',$id)
                ->select('employees.id AS id','employees.name AS name','schedule_assign.start_date AS startDate','schedule_assign.end_date AS endDate','schedule_day.day_name AS dayName','schedule_day.entry AS entry','schedule_day.departure AS departure','schedule_template.name AS templateName','schedule_assign.order_gs AS orden','schedule_assign.id AS idAssign')
                ->orderBy('employees.id')
                ->orderBy('schedule_assign.order_gs')
                ->orderBy('schedule_day.day_num')
                ->get(); 

        return view('assign.editprogramming')->with('assigns',$assigns);       

    }

    public function actualizar (Request $request,$id){
        $assigns = DB::table('schedule_assign')
                ->where('id',$id)
                ->select('id AS id','group_schedules_id AS group')
                ->get();
        $aux = 0;
        if($assigns[0]->group == NULL){
            $datas = assign_schedule::find($id);
            $datas->start_date = $request->start_date;
            $datas->end_date = $request->end_date;
            $datas->order_gs = $request->orden1;
            $datas->save();

            $data = assign_schedule::find($id);
            $datas->start_date = $request->end_date;
             
        }else{
            $asignaciones = DB::table('schedule_assign')
                    ->where('group_schedules_id',$assigns[0]->group)
                    ->select('id AS id')
                    ->get();
            for($i = 0 ; count($asignaciones) > $i ; $i++){
                $aux++;
                $orden = 'orden'.$aux;
                $datas = assign_schedule::find($asignaciones[$i]->id);
                $datas->start_date = $request->start_date;
                $datas->end_date = $request->end_date;
                $datas->order_gs = $request->$orden;
                $datas->save();
            }
            
        }  
        $url =  'assigns/viewProgramming';
        return redirect($url)->with('mensaje','Asignación fue actualizada con éxito');
    }

    public function viewProgramming (){

        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        if (session()->get('rol_id') != 1){
            $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
            $Adgu = [];
            $adguString="";
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
                if($i == 0){
                    $adguString = $adguString.$dgu[$i]->id;
                }else{
                    $adguString = $adguString.", ".$dgu[$i]->id;
                }
                
            }
            /*$assigns = DB::table('schedule_assign')
                ->join('employees','employees.id','=','schedule_assign.employee_id')
                ->join('schedule_template','schedule_template.id','=','schedule_assign.schedule_template_id')
                ->join('jobs','jobs.id','=','employees.job_id')
                ->join('departments','departments.id','=','jobs.department_id')
                ->where('schedule_assign.is_delete',0)
                ->where('schedule_assign.schedule_template_id','!=',$iTemplateId)
                ->whereIn('departments.dept_group_id',$Adgu)
                ->select('schedule_template.name AS template','employees.id AS id','employees.name AS name','schedule_assign.start_date AS startDate','schedule_assign.end_date AS endDate','schedule_assign.id AS idAssign')
                ->orderBy('employees.id')
                ->orderBy('schedule_assign.group_schedules_id')
                ->groupBy('employees.id')
                ->get(); 
              */  
                $assigns = DB::select("SELECT a.fecha_inicio, a.fecha_fin, b.name AS nombreEmpleado, s.name AS nombreHorario, a.group_assign_id, a.id
                FROM (
                    SELECT start_date as fecha_inicio, end_date as fecha_fin ,p.employee_id, schedule_template_id, group_assign_id,id FROM schedule_assign p WHERE is_delete = 0 AND schedule_template_id != ".$iTemplateId." ORDER BY p.employee_id, start_date DESC
                ) a
                INNER JOIN employees b ON a.employee_id = b.id
                INNER JOIN schedule_template s ON a.schedule_template_id = s.id
                INNER JOIN jobs j ON b.job_id = j.id
                INNER JOIN departments d ON b.department_id = d.id
                INNER JOIN department_group g ON d.dept_group_id = g.id
                WHERE b.is_active = 1
                AND g.id IN (".$adguString. ")
                 GROUP By a.employee_id");
        }else{
            $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
            /*$assigns = DB::table('schedule_assign')
                ->join('employees','employees.id','=','schedule_assign.employee_id')
                ->join('schedule_template','schedule_template.id','=','schedule_assign.schedule_template_id')
                ->join('jobs','jobs.id','=','employees.job_id')
                ->join('departments','departments.id','=','jobs.department_id')
                ->where('schedule_assign.is_delete',0)
                ->where('schedule_assign.schedule_template_id','!=',$iTemplateId)
                ->select('schedule_template.name AS template','employees.id AS id','employees.name AS name','schedule_assign.start_date AS startDate','schedule_assign.end_date AS endDate','schedule_assign.id AS idAssign')
                ->orderBy('employees.name')
                ->orderBy('schedule_assign.group_schedules_id')
                ->groupBy('employees.id')
                ->get();
            */ 
                $assigns = DB::select("SELECT a.fecha_inicio, a.fecha_fin, b.name AS nombreEmpleado, s.name AS nombreHorario, a.group_assign_id, a.id
                FROM (
                    SELECT start_date as fecha_inicio, end_date as fecha_fin ,p.employee_id, schedule_template_id, group_assign_id,id FROM schedule_assign p WHERE is_delete = 0 AND schedule_template_id != ".$iTemplateId." ORDER BY p.employee_id, start_date DESC
                ) a
                INNER JOIN employees b ON a.employee_id = b.id
                INNER JOIN schedule_template s ON a.schedule_template_id = s.id
                INNER JOIN jobs j ON b.job_id = j.id
                INNER JOIN departments d ON b.department_id = d.id
                INNER JOIN department_group g ON d.dept_group_id = g.id
                WHERE b.is_active = 1
                GROUP By a.employee_id");    
        }
        return view('assign.showprogramming')->with('assigns',$assigns)->with('dgroup',$usuario[0]->id);
    }

    public function viewDayprogram (){

        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        if (session()->get('rol_id') != 1){
            $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
            $Adgu = [];
            $adguString="";
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
                
            }
                $assigns = DB::table('schedule_assign')
                                ->join('employees','employees.id','=','schedule_assign.employee_id')
                                ->join('schedule_template', 'schedule_template.id','=','schedule_assign.schedule_template_id')
                                ->join('departments','departments.id','=','employees.department_id')
                                ->join('department_group','departments.dept_group_id','=','department_group.id')
                                ->where('employees.is_active',1)
                                ->where('schedule_assign.is_delete',0)
                                ->where('schedule_assign.schedule_template_id',$iTemplateId)
                                ->whereIn('department_group.id',$Adgu)
                                ->select('employees.name AS nombreEmpleado','schedule_assign.start_date AS fecha_inicio','schedule_template.name AS nombreHorario','schedule_assign.id AS id')
                                ->get();
        }else{
            $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
            
                    $assigns = DB::table('schedule_assign')
                    ->join('employees','employees.id','=','schedule_assign.employee_id')
                    ->join('schedule_template', 'schedule_template.id','=','schedule_assign.schedule_template_id')
                    ->join('departments','departments.id','=','employees.department_id')
                    ->join('department_group','departments.dept_group_id','=','department_group.id')
                    ->where('employees.is_active',1)
                    ->where('schedule_assign.is_delete',0)
                    ->where('schedule_assign.schedule_template_id',$iTemplateId)
                    ->select('employees.name AS nombreEmpleado','schedule_assign.start_date AS fecha_inicio','schedule_template.name AS nombreHorario','schedule_assign.id AS id')
                    ->get();    
        }
        return view('assign.dayprogramm')->with('assigns',$assigns)->with('dgroup',$usuario[0]->id);
    }

    public function viewSpecificDate () {
        if (session()->get('rol_id') != 1) {
            $numero = session()->get('name');
            $usuario = DB::table('users')
                ->where('name', $numero)
                ->get();

            $dgu = DB::table('group_dept_user')
                ->where('user_id', $usuario[0]->id)
                ->select('groupdept_id AS id')
                ->get();

            $Adgu = [];
            for ($i = 0; count($dgu) > $i; $i++) {
                $Adgu[$i] = $dgu[$i]->id;
            }

            $employees = DB::table('schedule_assign')
                ->join('employees', 'employees.id', '=', 'schedule_assign.employee_id')
                ->join('jobs', 'jobs.id', '=', 'employees.job_id')
                ->join('departments', 'departments.id', '=', 'jobs.department_id')
                ->whereIn('departments.dept_group_id', $Adgu)
                ->select('employees.id AS id', 'employees.name AS name')
                ->groupBy('employees.id')
                ->get();
        }
        else {
            $employees = DB::table('schedule_assign')
                ->join('employees', 'employees.id', '=', 'schedule_assign.employee_id')
                ->join('jobs', 'jobs.id', '=', 'employees.job_id')
                ->join('departments', 'departments.id', '=', 'jobs.department_id')
                ->select('employees.id AS id', 'employees.name AS name')
                ->groupBy('employees.id')
                ->get();
        }

        return view('assign.specificDate')->with('employees', $employees);
    }

    public function mostrarFecha (Request $request) {
        $semana = $request->semana;
        $año = explode('-', $semana);
        $weeks = explode('W', $semana);
        $year = $año[0];
        $week = $weeks[1];
        $calendario = null;
        $info = 0;

        $now = Carbon::now();
        $now->setISODate($year, $week);
        // Devuelve un array con ambas fechas
        // $fechas =  [$fecha, $fecha2];
        
        $fechas = [new DateTime($now->copy()->startOfWeek()->toDateString()), new DateTime($now->copy()->endOfWeek()->toDateString())];

        //termina determinacion de fechas
        $grupo = '';
        $comparacion = 0;
        $diferencia = 52;
        $id = 0;
        $employees = $request->empleado;
        $data = [];
        $fechaInicio = $fechas[0]->format('Y-m-d');
        $fechaFin = $fechas[1]->format('Y-m-d');
        foreach ($employees as $employeeId) {
            $assign = DB::table('schedule_assign')
                ->join('employees', 'employees.id', '=', 'schedule_assign.employee_id')
                ->where('employees.id', $employeeId)
                ->where('schedule_assign.is_delete', 0)
                ->where('start_date','<=',$fechaInicio)
                ->Where(function($query) use ($fechaFin) {
                        $query->where('end_date','>=',$fechaFin)
                        ->orwhereNull('end_date');
                })
                ->select('schedule_assign.start_date AS Start', 'schedule_assign.end_date AS End', 'group_schedules_id AS Grupo', 'schedule_assign.id AS Id')
                ->orderBy('schedule_assign.updated_at')
                ->get();


            /*
            if ( $numeroSemana == $week && $anio == $year ) {
                $diferencia = 0; 
            } 
            else {
                if ($numeroSemana < $week && $year == $anio ) {
                        $comparacion =  $week - $numeroSemana;
                        if ($comparacion < $diferencia) {
                            $diferencia = $comparacion;
                        }
                } else if ($anio < $year) {
                    $difAnios = $year - $anio;
                    
                    while ($difAnios >= 0){

                    }
                }
            }
            */
            $id = $assign[0]->Id;
            $grupo = $assign[0]->Grupo;
            
            if ($grupo != null) {
                $assign = DB::table('schedule_assign')
                    ->join('employees', 'employees.id', '=', 'schedule_assign.employee_id')
                    ->where('schedule_assign.group_schedules_id', $grupo)
                    ->where('schedule_assign.is_delete', 0)
                    ->select('schedule_assign.start_date AS Start', 'schedule_assign.end_date AS End', 'group_schedules_id AS Grupo', 'schedule_assign.id AS Id')
                    ->get();
            } else {
                if ($id != 0) {
                    $assign = DB::table('schedule_assign')
                        ->join('employees', 'employees.id', '=', 'schedule_assign.employee_id')
                        ->where('schedule_assign.id', $id)
                        ->where('schedule_assign.is_delete', 0)
                        ->select('schedule_assign.start_date AS Start', 'schedule_assign.end_date AS End', 'group_schedules_id AS Grupo', 'schedule_assign.id AS Id')
                        ->get();
                }
            }

            if (count($assign) == 1) {

                $fechaAux1 = date_format($fechas[0], 'd-m-Y');
                $fechaAux2 = date_format($fechas[1], 'd-m-Y');

                $calendario = DB::table('schedule_assign')
                    ->join('employees', 'employees.id', '=', 'schedule_assign.employee_id')
                    ->join('schedule_template', 'schedule_template.id', '=', 'schedule_assign.schedule_template_id')
                    ->join('schedule_day', 'schedule_day.schedule_template_id', '=', 'schedule_template.id')
                    ->where('employees.id', $employeeId)
                    ->where('schedule_assign.id', $id)
                    ->where('schedule_assign.is_delete', 0)
                    ->select('schedule_day.day_name AS Dia', 'schedule_day.day_num AS Numero', 'schedule_day.entry AS Entrada', 'schedule_day.departure AS Salida', 'employees.id AS Id', 'employees.name AS Nombre')
                    ->get();
                $info = 1;

            } else {
                $fechaIni = strtotime($assign[0]->Start);
                $fechaFin = strtotime($assign[0]->End);
                $fechaAux1 = date_format($fechas[0], 'd-m-Y');
                $fechaAux2 = date_format($fechas[1], 'd-m-Y');
                $fechaComparacion = strtotime($fechaAux1);
                $fechaComparacion2 = strtotime($fechaAux2);
                $diferenciaSemanas = 0;
                if (!$fechaFin || $fechaFin <= $fechaComparacion2) {
                    if ($fechaIni <= $fechaComparacion) {
                        $numeroHorarios = count($assign);
                        $horario = 1;
                        $dia   = substr($assign[0]->Start, 8, 2);
                        $mes = substr($assign[0]->Start, 5, 2);
                        $anio = substr($assign[0]->Start, 0, 4);
                        $numeroSemana = date("W", mktime(0, 0, 0, $mes, $dia, $anio));
                        if($year > $anio){
                            $diferenciaAnios = $year - $anio;
                            $diferenciaSemanas = $diferencia - $numeroSemana;
                            $diferenciaAnios --;
                            while ($diferenciaAnios > 0){
                                $diferenciaSemanas += $diferencia;
                                $diferenciaAnios--;
                            }
                            $diferernciaSemanas += $week;
                        } else {
                            $diferenciaSemanas = $week - $numeroSemana;
                        }
                        for ($i = 0 ; $diferenciaSemanas > $i ; $i++ ) {
                            $horario++;
                            if ($horario > $numeroHorarios) {
                                $horario = 1;
                            }
                        }
                        $calendario = DB::table('schedule_assign')
                            ->join('employees', 'employees.id', '=', 'schedule_assign.employee_id')
                            ->join('schedule_template', 'schedule_template.id', '=', 'schedule_assign.schedule_template_id')
                            ->join('schedule_day', 'schedule_day.schedule_template_id', '=', 'schedule_template.id')
                            ->where('employees.id', $employeeId)
                            ->where('schedule_assign.order_gs', $horario)
                            ->where('schedule_assign.is_delete', 0)
                            ->select('schedule_day.day_name AS Dia', 'schedule_day.day_num AS Numero', 'schedule_day.entry AS Entrada', 'schedule_day.departure AS Salida', 'employees.id AS Id', 'employees.name AS Nombre')
                            ->get();
                        $info = 1;
                    }
                }
            }

            $data[] = $calendario;
        }

        $Empleado = $request->empleado;
        $diff = $fechas[0]->diff($fechas[1]);
        $diff = $diff->days;

        return view('assign.viewCalendar')
            ->with('calendario', $data)
            ->with('empleado', $Empleado)
            ->with('inicio', $fechaAux1)
            ->with('fin', $fechaAux2)
            ->with('diff', $diff)
            ->with('info', $info);
    }

    function get_dates($year = 0, $week = 0)
    {
       
    }

    function employeesWithout(){
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        if (session()->get('rol_id') != 1){
            $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
            $Adgu = [];
            $adguString="";
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
                if($i == 0){
                    $adguString = $adguString.$dgu[$i]->id;
                }else{
                    $adguString = $adguString.", ".$dgu[$i]->id;
                }
                
            }
            $assigns = DB::table('schedule_assign')
                                ->join('employees','employees.id' , '=', 'schedule_assign.employee_id')
                                ->join('departments','departments.id', '=', 'employees.department_id')
                                ->join('department_group','department_group.id','=','departments.dept_group_id')
                                ->where('schedule_assign.is_delete',0)
                                ->where('schedule_assign.schedule_template_id',"!=",$iTemplateId)
                                ->whereIn('department_group.id',$Adgu)
                                ->select('employees.id AS idEmployee')
                                ->get();
            $employees = [];
            for($i = 0 ; count($assigns) > $i ; $i++){
                $employees[$i] = $assigns[$i]->idEmployee;
            }
            $employees = DB::table('employees')
                                    ->whereNotIn('employees.id',$employees)
                                    ->join('departments','departments.id', '=', 'employees.department_id')
                                    ->join('department_group','department_group.id','=','departments.dept_group_id')
                                    ->whereIn('department_group.id',$Adgu)
                                    ->select('employees.id AS idEmployee','employees.name AS nameEmployee','departments.name AS nameDept')
                                    ->groupBy('employees.id')
                                    ->get();
        }else{
            $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
            $assigns = DB::table('schedule_assign')
                    ->join('employees','employees.id' , '=', 'schedule_assign.employee_id')
                    ->join('departments','departments.id', '=', 'employees.department_id')
                    ->join('department_group','department_group.id','=','departments.dept_group_id')
                    ->where('schedule_assign.is_delete',0)
                    ->where('schedule_assign.schedule_template_id',"!=",$iTemplateId)
                    ->select('employees.id AS idEmployee')
                    ->get();
            $employees = [];
            for($i = 0 ; count($assigns) > $i ; $i++){
                $employees[$i] = $assigns[$i]->idEmployee;
            }
            $employees = DB::table('employees')
                        ->whereNotIn('employees.id',$employees)
                        ->join('departments','departments.id', '=', 'employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->groupBy('employees.id')
                        ->select('employees.id AS idEmployee','employees.name AS nameEmployee','departments.name AS nameDept')
                        ->get();   
        }
        return view('assign.employeeswithout')->with('datas',$employees)->with('dgroup',$usuario[0]->id);   
    }

    public function withoutcreate($id){
        $iTemplateId = env('TMPLTE_SATURDAYS', 0);
        $employee = employees::where('is_delete','0')->where('is_active', true)->where('id',$id)->orderBy('name','ASC');
        $schedule_template = schedule_template::where('is_delete','0')->where('id','!=',$iTemplateId)->orderBy('name','ASC')->pluck('id','name');

        return view('assign.without')->with('employee',$employee)->with('schedule_template',$schedule_template);    
    }

    public function guardarw(Request $request){
        $start = null;
        $end = null;
        $orden = null;
        $group_num = null;

        if($request->start_date != ''){
            $start = $request->start_date;
            $end = $request->end_date;
        }
            
        $asignacion = new assign_schedule();
        $asignacion->employee_id = $request->idemp;
        $asignacion->schedule_template_id = $request->horario;
        $asignacion->start_date = $start;
        $asignacion->end_date = $start;
        $asignacion->order_gs = 1;
        $asignacion->created_by = session()->get('user_id');
        $asignacion->updated_by = session()->get('user_id');
        $asignacion->save();    
    }

}

