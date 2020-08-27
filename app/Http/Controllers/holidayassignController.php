<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\assign_schedule;
use App\Models\schedule_template;
use App\Models\schedule_day;
use App\Models\department;
use App\Models\area;
use App\Models\employees;
use App\Models\group_assign;
use App\Models\holidayAux;
use App\Models\holiday;
use App\SUtils\SDateTimeUtils;
use App\Models\groupSchedule;
use App\Models\holidayassign;
use App\SUtils\SEventsUtils;
use DateTime;
use DB;
use DatePeriod;
use DateInterval;

class holidayassignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = holidayassign::where('is_delete','0')->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->department;
            $datas->employee;
            $datas->area;
            $datas->holiday;
        });
        return view('holidayassign.index', compact('datas')); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tipo)
    {
        $employee = employees::where('is_delete','0')->where('is_active', true)->orderBy('name','ASC')->pluck('id','name');
        $department = department::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $area = area::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $year = holiday::where('is_delete','0')->groupBy('year')->select('year AS year')->get();
        $holiday = holiday::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');

        return view('holidayassign.create')->with('employee',$employee)->with('department',$department)->with('holiday',$holiday)->with('area',$area)->with('flag',0)->with('tipo',$tipo)->with('year',$year);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $date = null;
        if($request->date != ''){
            $date = $request->date;
        }
        else {
            return back()->withInput()->withErrors(['El campo fecha es obligatorio']);
        }

        switch($request->tipo){
            case 1:
            $empleados = $request->empleado;
            if(count($empleados) == 1){
                $events = SEventsUtils::getAllEvents($date, $empleados[0], 0, 0, 0);

                if (count($events) > 0) {
                    return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
                }

                $holiday = new holidayassign();
                $holiday->employee_id = $empleados[0];
                $holiday->holiday_id = $request->festivo;
                $holiday->date = $date;
                $holiday->created_by = 1;
                $holiday->updated_by = 1;
                $holiday->save();
    
            }else{
                $grupo = new group_assign();
                $grupo->created_by = 1;
                $grupo->updated_by = 1;
                $grupo->save();

                $events = SEventsUtils::getAllEvents($date, $empleados, 0, 0, 0);

                if (count($events) > 0) {
                    return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
                }
    
                for($y = 0 ; count($empleados) > $y ; $y++){
                    $holiday = new holidayassign();
                    $holiday->employee_id = $empleados[$y];
                    $holiday->date = $date;
                    $holiday->holiday_id = $request->festivo;
                    $holiday->group_assign_id = $grupo->id;
                    $holiday->created_by = 1;
                    $holiday->updated_by = 1;
                    $holiday->save();
                }
    
            }
        break;
        case 2:
            $events = SEventsUtils::getAllEvents($date, null, $request->departamento, 0, 0);

            if (count($events) > 0) {
                return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha para este departamento']);
            }

            $holiday = new holidayassign();
            $holiday->department_id = $request->departamento;
            $holiday->holiday_id = $request->festivo;
            $holiday->date = $date;
            $holiday->created_by = 1;
            $holiday->updated_by = 1;
            $holiday->save();   
        break;
        case 3:
            $events = SEventsUtils::getAllEvents($date, null, 0, 0, $request->area);

            if (count($events) > 0) {
                return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha para este área']);
            }

            $holiday = new holidayassign();
            $holiday->area_id = $request->area;
            $holiday->holiday_id = $request->festivo;
            $holiday->date = $date;
            $holiday->created_by = 1;
            $holiday->updated_by = 1;
            $holiday->save(); 
        break;

    }
        
        return redirect('holidayassign')->with('mensaje','Asignación Día Festivo fue creada con exito');
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
        $employee = employees::where('is_delete','0')->where('is_active', true)->orderBy('name','ASC')->pluck('id','name');
        $department = department::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $area = area::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $holiday = holiday::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $datas = holidayassign::find($id);
        $auxiliar = 0;
        $empleados = 0;
        if($datas->employee_id == 0){
            if($datas->department_id == 0){
                $tipo = 3;
            }else{
                $tipo = 2;
            }
        }else{
            $tipo = 1;
        }
        $flag = 0;
        if($datas->group_assign_id != null){
            $auxiliar = $datas->group_assign_id;
            $empleados = DB::table('holiday_assign')
                        ->join('group_assign','holiday_assign.group_assign_id','=','group_assign.id')
                        ->where('group_assign_id',$auxiliar)
                        ->select('employee_id AS idEmp','holiday_assign.id AS idHoliday','department_id AS idDepartment','area_id AS idArea')
                        ->get();
            $flag = 1;
        }

        return view('holidayassign.edit', compact('datas'))->with('holiday',$holiday)->with('department',$department)->with('employee',$employee)->with('flag',$flag)->with('tipo',$tipo)->with('empleados',$empleados)->with('auxiliar',$auxiliar)->with('area',$area);
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
        $date = null;
        if($request->date != ''){
            $date = $request->date;
        }
        switch($request->tipo){
            case 1:
            $empleados = $request->empleado;
            if($request->group == 0){
                holidayassign::where('id', $id)->delete();
                if(count($empleados) == 1){

                    $events = SEventsUtils::getAllEvents($date, $empleados[0], 0, 0, 0);

                    if (count($events) > 0) {
                        return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
                    }

                    $holiday = new holidayassign();
                    $holiday->employee_id = $empleados[0];
                    $holiday->holiday_id = $request->festivo;
                    $holiday->date = $date;
                    $holiday->created_by = 1;
                    $holiday->updated_by = 1;
                    $holiday->save();    
                }else{
                    $grupo = new group_assign();
                    $grupo->created_by = 1;
                    $grupo->updated_by = 1;
                    $grupo->save();

                    $events = SEventsUtils::getAllEvents($date, $empleados, 0, 0, 0);

                    if (count($events) > 0) {
                        return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
                    }
    
                    for($y = 0 ; count($empleados) > $y ; $y++){
                        $holiday = new holidayassign();
                        $holiday->employee_id = $empleados[$y];
                        $holiday->date = $date;
                        $holiday->holiday_id = $request->festivo;
                        $holiday->group_assign_id = $grupo->id;
                        $holiday->created_by = 1;
                        $holiday->updated_by = 1;
                        $holiday->save();
                    }
                }
            }else{
                holidayassign::where('group_assign_id', $request->group)->delete();
                if(count($empleados) == 1){
                    $events = SEventsUtils::getAllEvents($date, $empleados[0], 0, 0, 0);

                    if (count($events) > 0) {
                        return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
                    }

                    $group_assign = group_assign::find($request->group);
                    $group_assign->is_delete = 1;
                    $group_assign->save();
                    $holiday = new holidayassign();
                    $holiday->employee_id = $empleados[0];
                    $holiday->holiday_id = $request->festivo;
                    $holiday->date = $date;
                    $holiday->created_by = 1;
                    $holiday->updated_by = 1;
                    $holiday->save();    
                }else{
                    $events = SEventsUtils::getAllEvents($date, $empleados, 0, 0, 0);

                    if (count($events) > 0) {
                        return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
                    }

                    for($y = 0 ; count($empleados) > $y ; $y++){
                        $holiday = new holidayassign();
                        $holiday->employee_id = $empleados[$y];
                        $holiday->date = $holiday;
                        $holiday->holiday_id = $request->festivo;
                        $holiday->group_assign_id = $request->group;
                        $holiday->created_by = 1;
                        $holiday->updated_by = 1;
                        $holiday->save();
                    }      
                }
            }
        break;
        case 2:
            $events = SEventsUtils::getAllEvents($date, null, $request->departamento, 0, 0, $id);

            if (count($events) > 0) {
                return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
            }
            $holiday = holidayassign::find($id);
            $holiday->department_id = $request->departamento;
            $holiday->holiday_id = $request->festivo;
            $holiday->date = $date;
            $holiday->created_by = 1;
            $holiday->updated_by = 1;
            $holiday->save();       
        break; 
        case 3:
            $events = SEventsUtils::getAllEvents($date, null, 0, 0, $request->area, $id);

            if (count($events) > 0) {
                return back()->withInput()->withErrors(['Ya hay incidencias o días festivos para esta fecha']);
            }
            $holiday = holidayassign::find($id);
            $holiday->area_id = $request->area;
            $holiday->holiday_id = $request->festivo;
            $holiday->date = $date;
            $holiday->created_by = 1;
            $holiday->updated_by = 1;
            $holiday->save();   
        break;
        }

        return redirect('holidayassign')->with('mensaje','Asignación fue actualizada con exito');  
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
            $datas = holidayassign::find($id);
            $auxiliar = $datas->group_assign_id;
            if($auxiliar != null){
                $empleados = DB::table('holiday_assign')
                        ->join('group_assign','holiday_assign.group_assign_id','=','group_assign.id')
                        ->where('group_assign_id',$auxiliar)
                        ->select('holiday_assign.id AS id')
                        ->get();
                for($i = 0 ; count($empleados) > $i ; $i++){
                    $datas = holidayassign::find($empleados[$i]->id);
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

    public function recoverHoliday(Request $request){
        $holidays = DB::table('holidays')
        ->where('holidays.year',$request->anio)
        ->select('fecha AS fecha','name AS name','id AS id')
        ->get();

        return response()->json($holidays);
    }
}
