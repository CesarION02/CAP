<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\assign_schedule;
use App\Models\schedule_template;
use App\Models\schedule_day;
use App\Models\department;
use App\Models\employees;
use App\Models\group_assign;
use DateTime;
use DB;

class assignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = assign_schedule::where('is_delete','0')->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->department;
            $datas->employee;
            $datas->schedule;
        });
        return view('assign.index', compact('datas'));    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tipo)
    {
        $employee = employees::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $department = department::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $schedule_template = schedule_template::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');

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
                $asignacion->created_by = 1;
                $asignacion->updated_by = 1;
                $asignacion->save();
    
            }else{
                $grupo = new group_assign();
                $grupo->created_by = 1;
                $grupo->updated_by = 1;
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
            $asignacion->created_by = 1;
            $asignacion->updated_by = 1;
            $asignacion->save();   
        }
        
        return redirect('assign')->with('mensaje','Asignación fue creada con exito');   
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
        $employee = employees::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $department = department::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $schedule_template = schedule_template::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
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
                    $asignacion->created_by = 1;
                    $asignacion->updated_by = 1;
                    $asignacion->save();    
                }else{
                    $grupo = new group_assign();
                    $grupo->created_by = 1;
                    $grupo->updated_by = 1;
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
                    $asignacion->created_by = 1;
                    $asignacion->updated_by = 1;
                    $asignacion->save();    
                }else{
                    for($y = 0 ; count($empleados) > $y ; $y++){
                        $asignacion = new assign_schedule();
                        $asignacion->employee_id = $empleados[$y];
                        $asignacion->start_date = $start;
                        $asignacion->end_date = $end;
                        $asignacion->schedule_template_id = $request->horario;
                        $asignacion->group_assign_id = $request->group;
                        $asignacion->created_by = 1;
                        $asignacion->updated_by = 1;
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
            $assign->created_by = 1;
            $assign->updated_by = 1;
            $assign->save();       
        }   
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

    public function indexOneDay(int $var = null)
    {
        $lSchedules = $this->getData();

        $lEmployees = employees::where('is_delete', false)
                                ->select('id', 'num_employee', 'name')
                                ->orderBy('name', 'ASC')
                                ->orderBy('num_employee', 'ASC')
                                ->get();

        $iTemplateId = 1;
        $iGrpSchId = 1;

        return view('scheduleone.index')->with('lSchedules', $lSchedules)
                                        ->with('lEmployees', $lEmployees)
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
                $lAssigns = assign_schedule::where('schedule_template_id', 1)
                                ->where('group_assign_id', 1)
                                ->where('group_schedules_id', 1)
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
                        $oNew->group_assign_id = 1;
                        $oNew->schedule_template_id = 1;
                        $oNew->group_schedules_id = 1;
                        $oNew->start_date = $newDate;
                        $oNew->end_date = $newDate;
                        $oNew->is_delete = false;
                        $oNew->order_gs = 0;
                        $oNew->created_by = 1;
                        $oNew->updated_by = 1;

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
                $oToReplace->updated_by = 1;
                $oToReplace->save();

            case 0:
                $oAssign = json_decode($request->ass_objs);

                $obj = new assign_schedule();

                $obj->employee_id = $oAssign->employee_id;
                $obj->group_assign_id = 1;
                $obj->schedule_template_id = 1;
                $obj->start_date = $oAssign->start_date;
                $obj->end_date = $oAssign->start_date;
                $obj->group_schedules_id = 1;
                $obj->order_gs = 0;
                $obj->is_delete = false;
                $obj->created_by = 1;
                $obj->updated_by = 1;

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
        $oAssing->updated_by = 1;
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
        $oAssing->updated_by = 1;
        $oAssing->save();

        // $lSchedules = $this->getData();
        $lSchedules = $oAssing;

        return json_encode($lSchedules);
    }

    public function getData()
    {
        return DB::table('schedule_assign AS sa')
                            ->join('employees AS e', 'e.id', '=', 'sa.employee_id')
                            ->join('group_schedule AS gs', 'gs.id', '=', 'sa.group_schedules_id')
                            ->select('e.name', 
                                        'e.num_employee', 
                                        'sa.start_date', 
                                        'sa.end_date', 
                                        'sa.group_schedules_id', 
                                        'sa.order_gs',
                                        'sa.employee_id',
                                        'sa.id'
                                    )
                            ->where('sa.is_delete', false)
                            ->where('schedule_template_id', 1)
                            ->where('group_assign_id', 1)
                            ->where('group_schedules_id', 1)
                            ->orderBy('start_date', 'ASC')
                            ->orderBy('order_gs', 'ASC')
                            ->get();
    }
}
