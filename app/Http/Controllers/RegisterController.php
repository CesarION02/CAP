<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\register;
use App\Models\Dateregister;
use Carbon\Carbon;
use DB;
use App\Models\Bitacora;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        $start_date = null;
        $end_date = null;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $start_date = $now->startOfMonth()->toDateString();
            $end_date = $now->endOfMonth()->toDateString();
        }
        else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }

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
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->join('registers','employees.id','=','registers.employee_id')
                        ->join('users','registers.user_id','=','users.id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('registers.form_creation_id','2')
                        ->where('registers.is_delete','0')
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->whereBetween('date', [$start_date, $end_date])
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','registers.id AS id','registers.date AS date','registers.time AS time','registers.type_id AS tipo','users.name AS usuario')
                        ->get();
        }else{
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->join('registers','employees.id','=','registers.employee_id')
                        ->join('users','registers.user_id','=','users.id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('registers.form_creation_id','2')
                        ->where('registers.is_delete','0')
                        ->whereBetween('date', [$start_date, $end_date])
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','registers.id AS id','registers.date AS date','registers.time AS time','registers.type_id AS tipo','users.name AS usuario')
                        ->get();   
        }
        return view('register.index', compact('employees'))
                                    ->with('start_date', $start_date)
                                    ->with('end_date', $end_date);
    }

    public function getRegistry(Request $request){
        $lRegistries = \DB::table('registers')
                        ->where('employee_id', $request->employee_id)
                        ->where('date',$request->date)
                        ->select('date','time','type_id')
                        ->get();
        
        return response()->json(['lRegistries' => $lRegistries]);
    }

    public function create()
    {
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
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.id AS id', 'employees.num_employee AS numEmployee')
                        ->get();
        }else{
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.id AS id', 'employees.num_employee AS numEmployee')
                        ->get();   
        }
        
        return view('register.create')->with('employees',$employees);
    }

    public function store(Request $request)
    {
        $register = new register();

        $register->employee_id = $request->employee_id;
        $register->date = $request->date;
        $register->form_creation_id = 2;
        $register->user_id = session()->get('user_id');
        $register->time = $request->time;
        $register->type_id = $request->type_id;

        $dateregister = new Dateregister();
        $dateregister->updated_by = session()->get('user_id');
        $dateregister->created_by = session()->get('user_id');
        $dateregister->is_delete = 0;

        

        // si solo se va a insertar una checada
        if ($request->optradio == "single") {
            $register->save();

            $dateregister->register_id = $register->id;
            $dateregister->save();
        }
        // si se va a insertar un corte (entrada y salida)
        else {
            $oDate = Carbon::parse($request->date.' '.$request->time);

            $register1 = clone $register;

            $register->type_id = 1;
            $register->save();

            $oDate->addSecond();
            $register1->date = $oDate->toDateString();
            $register1->time = $oDate->toTimeString();
            $register1->type_id = 2;
            $register1->save();

            $dateregister1 = clone $dateregister;

            $dateregister->register_id = $register->id;
            $dateregister->save();

            $dateregister1->register_id = $register1->id;
            $dateregister1->save();
        }

        $bitacora = new Bitacora();
        $bitacora->tipo = "Crear";
        $bitacora->usuario_id = session()->get('user_id');
        $bitacora->register_id = $register->id;
        $bitacora->date = $register->date;
        $bitacora->time = $register->time;
        $bitacora->type = $register->type_id;
        $bitacora->save();

        return redirect('register')->with('mensaje','Checada creada con éxito');
    }

    public function edit($id)
    {
        $datas = register::findOrFail($id);
        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('employees.id', $datas->employee_id)
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.id AS id', 'employees.num_employee AS numEmployee')
                        ->get();

        return view('register.edit', compact('datas'))
                                        ->with('employees',$employees);
    }

    public function update(Request $request, $id)
    {
        $register = register::findOrFail($id);
        $register->employee_id = $request->employee_id;
        $register->date = $request->date;
        $register->time = $request->time;
        $register->type_id = $request->type_id;
        $register->form_creation_id = 2;
        $register->is_modified = true;
        $register->user_id = session()->get('user_id');
        $register->save();

        $iddateregister = Dateregister::where('register_id','=',$id)->get();
        $dateregister = Dateregister::find($iddateregister[0]->id);
        $dateregister->updated_by = session()->get('user_id');
        
        $bitacora = new Bitacora();
        $bitacora->tipo = "Modificar";
        $bitacora->usuario_id = session()->get('user_id');
        $bitacora->register_id = $register->id;
        $bitacora->date = $register->date;
        $bitacora->time = $register->time;
        $bitacora->type = $register->type_id;

        $dateregister->save();


        return redirect('register')->with('mensaje', 'Checador actualizado con éxito');
    }

    public function destroy(Request $request,$id)
    {
        if ($request->ajax()) {
            $employee = register::find($id);
            $employee->is_delete = 1;
            $employee->is_modified = true;
            $employee->save();
            $iddateregister = Dateregister::where('register_id','=',$id)->get();
            $dateregister = Dateregister::find($iddateregister[0]->id);
            $dateregister->is_delete = 1;
            $dateregister->save();

            $bitacora = new Bitacora();
            $bitacora->tipo = "Eliminar";
            $bitacora->usuario_id = session()->get('user_id');
            $bitacora->register_id = $employee->id;
            $bitacora->date = $employee->date;
            $bitacora->time = $employee->time;
            $bitacora->type = $employee->type_id;
            $bitacora->save();

            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }

    public function registersEmployees(){
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
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.name')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        }else{
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->join('benefit_policies','benefit_policies.id','=','employees.ben_pol_id')
                        ->orderBy('employees.name')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();   
        }

        return view('register.registerEmployee', compact('employees'));
    }

    public function registers($id = 0, Request $request){

        $iFilter = $request->ifilter == 0 ? 1 : $request->ifilter;

        $start_date = null;
        $end_date = null;
        if ($request->start_date1 == null) {
            if($request->start_date == null){
                $now = Carbon::now();
                $start_date = $now->startOfMonth()->toDateString();
                $end_date = $now->endOfMonth()->toDateString();
            }
            else{
                $start_date = $request->start_date;
                $end_date = $request->end_date;    
            }
        }
        else {
            $start_date = $request->start_date1;
            $end_date = $request->end_date1;
        }
        switch($iFilter){
            case 1:
                $registros = DB::table('registers')
                    ->join('employees','employees.id','=','registers.employee_id')
                    ->orderBy('registers.date')
                    ->where('registers.employee_id',$id)
                    ->where('registers.is_delete',0)
                    ->whereBetween('date',[$start_date,$end_date])
                    ->select('employees.name AS nombre','registers.date AS fecha','registers.time AS hora','registers.type_id AS tipo','registers.id AS id')
                    ->get();
                break;
            case 2:
                $registros = DB::table('registers')
                    ->join('employees','employees.id','=','registers.employee_id')
                    ->orderBy('registers.date')
                    ->where('registers.employee_id',$id)
                    ->where('registers.is_delete',1)
                    ->whereBetween('date',[$start_date,$end_date])
                    ->select('employees.name AS nombre','registers.date AS fecha','registers.time AS hora','registers.type_id AS tipo','registers.id AS id')
                    ->get();
                break;    
        }
        
        
        return view('register.viewregisters')->with('registros',$registros)->with('start_date',$start_date)->with('end_date',$end_date)->with('id',$id)->with('iFilter',$iFilter);


    }

    public function registerEdit($id){
        $registros = DB::table('registers')
                    ->join('employees','employees.id','=','registers.employee_id')
                    ->orderBy('registers.date')
                    ->where('registers.id',$id)
                    ->select('employees.name AS nombre','registers.date AS fecha','registers.time AS hora','registers.type_id AS tipo','registers.id AS id','employees.id AS idemployee')
                    ->get();
        
        return view('register.editregister')->with('registros',$registros)->with('id',$registros[0]->idemployee);

    }

    public function registerUpdate(Request $request, $id){
        $register = register::findOrFail($id);
        //$register->date = $request->date;
        //$register->time = $request->time;
        $register->type_id = $request->type_id;
        $register->is_modified = true;
        $register->user_id = session()->get('user_id');
        $register->save();
        
        $bitacora = new Bitacora();
        $bitacora->tipo = "Modificar";
        $bitacora->usuario_id = session()->get('user_id');
        $bitacora->register_id = $register->id;
        $bitacora->date = $register->date;
        $bitacora->time = $register->time;
        $bitacora->type = $register->type_id;
        $bitacora->save();

        $link = 'registerEmployee/'.$request->id.'/view';

        return redirect($link)->with('mensaje', 'Checador actualizado con éxito');
    }

    public function registerDesactivar(Request $request,$id){
        if ($request->ajax()) {
            $employee = register::find($id);
            $employee->is_delete = 1;
            $employee->is_modified = true;
            $employee->save();

            $bitacora = new Bitacora();
            $bitacora->tipo = "Desactivar";
            $bitacora->usuario_id = session()->get('user_id');
            $bitacora->register_id = $employee->id;
            $bitacora->date = $employee->date;
            $bitacora->time = $employee->time;
            $bitacora->type = $employee->type_id;
            $bitacora->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }

    public function activar(Request $request,$id){
        if ($request->ajax()) {
            $job = register::find($id);
            $job->is_delete = 0;
            $job->is_modified = true;
            $job->save();

            $bitacora = new Bitacora();
            $bitacora->tipo = "Activar";
            $bitacora->usuario_id = session()->get('user_id');
            $bitacora->register_id = $job->id;
            $bitacora->date = $job->date;
            $bitacora->time = $job->time;
            $bitacora->type = $job->type_id;
            $bitacora->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }

    public function adjustRegistries(Request $request)
    {
        $bMIn = $request->modif_in;
        $bMOut = $request->modif_out;
        $oInDateTime = Carbon::parse($request->in_datetime);
        $oOutDateTime = Carbon::parse($request->out_datetime);

        $oRow = json_decode($request->row);

        $oInRegistry = null;
        $oOutRegistry = null;

        if ($bMIn) {
            if ($oRow->hasCheckIn) {
                $oOrigDate = Carbon::parse($oRow->inDateTime);

                $oInRegistry = register::where('employee_id', $oRow->idEmployee)
                                        ->where('date', $oOrigDate->toDateString())
                                        ->where('time', $oOrigDate->toTimeString())
                                        // ->where('type_id', 1)
                                        ->first();
            }
            else {
                $oInRegistry = new register();
                
                $oInRegistry->employee_id = $oRow->idEmployee;
                $oInRegistry->type_system = null;
                $oInRegistry->form_creation_id = null;
                $oInRegistry->is_delete = 0;
                $oInRegistry->biostar_id = 0;
                $oInRegistry->date_original = null;
                $oInRegistry->time_original = null;
                $oInRegistry->type_original = 1;
            }

            if ($oInRegistry != null) {
                $oInRegistry->date = $oInDateTime->toDateString();
                $oInRegistry->time = $oInDateTime->toTimeString();
                $oInRegistry->type_id = 1;
                $oInRegistry->is_modified = true;
                $oInRegistry->user_id = session()->get('user_id');
    
                $oInRegistry->save();
            }
        }

        if ($bMOut) {
            if ($oRow->hasCheckOut) {
                $oOrigDate = Carbon::parse($oRow->outDateTime);

                $oOutRegistry = register::where('employee_id', $oRow->idEmployee)
                                        ->where('date', $oOrigDate->toDateString())
                                        ->where('time', $oOrigDate->toTimeString())
                                        // ->where('type_id', 2)
                                        ->first();
            }
            else {
                $oOutRegistry = new register();
                
                $oOutRegistry->employee_id = $oRow->idEmployee;
                $oOutRegistry->type_system = null;
                $oOutRegistry->form_creation_id = null;
                $oOutRegistry->is_delete = 0;
                $oOutRegistry->biostar_id = 0;
                $oOutRegistry->date_original = null;
                $oOutRegistry->time_original = null;
                $oOutRegistry->type_original = 1;
            }

            if ($oOutRegistry != null) {
                $oOutRegistry->date = $oOutDateTime->toDateString();
                $oOutRegistry->time = $oOutDateTime->toTimeString();
                $oOutRegistry->type_id = 2;
                $oOutRegistry->is_modified = true;
                $oOutRegistry->user_id = session()->get('user_id');
    
                $oOutRegistry->save();
            }
        }

        return json_encode([$oInRegistry, $oOutRegistry]);
    }

    public function indexGenRegisters(){
        $startDate = Carbon::now()->startOfWeek()->toDateString();
        $endDate = Carbon::now()->endOfWeek()->toDateString();

        $employees = \DB::table('employees')
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->select('id', DB::raw("CONCAT(name,' - ',num_employee) AS name"))
                            ->get();

        $workships = \DB::table('workshifts')
                            ->where('is_delete',0)
                            ->select('id','name')
                            ->get();

        return view('genTurnos.index', ['employees' => $employees, 'start_date' => $startDate, 'end_date' => $endDate, 'workships' => $workships]);
    }

    public function registersGenerate(Request $request){
        $employee = \DB::table('employees')->where('id', $request->employee)->select('id','name','num_employee')->first();

        $registers = new \stdClass;
        $startDate = Carbon::parse($request->initDate);
        $endDate = Carbon::parse($request->finDate);
        $diff = $startDate->diffInDays($endDate);
        $diffWeeks = ($diff + 1)/7;
        $dias = [];
        $semanas = [];
        $numTurno = 0;
        $horaExtra = 0;
        $minutoExtra = 0;
        $extraHours = $request->has('extraHours');

        // $incidents = \DB::table('incidents_day as ind')
        //                 ->join('incidents as in', 'ind.incidents_id','=','in.id')
        //                 ->where([
        //                     ['in.employee_id',$employee->id],
        //                     ['in.start_date','>=',$startDate->format('Y-m-d')],
        //                     ['in.is_delete',0]
        //                 ])
        //                 ->get();

        $incidents = \DB::table('incidents')
                        ->where([
                            ['employee_id',$employee->id],
                            ['start_date','>=',$startDate->format('Y-m-d')],
                            ['is_delete',0]
                            ])
                        ->get();

        $holidays = \DB::table('holidays')
                        ->where([
                            ['fecha', '>=',$startDate->format('Y-m-d')],
                            ['is_delete',0]
                        ])
                        ->get();

        $registers->startDate = Carbon::parse($request->initDate)->format('Y-m-d');
        $registers->endDate = $endDate->format('Y-m-d');

        for ($k=0; $k < $diffWeeks; $k++) {
            $numTurno = $numTurno < sizeof($request->workships) ? $numTurno : $numTurno = 0;
            $horarios = \DB::table('workshifts')->where([['id',$request->workships[$numTurno]],['is_delete',0]])->first();
            $horaEntrada = Carbon::createFromFormat('H:i:s', $horarios->entry);
            $horaSalida = Carbon::createFromFormat('H:i:s', $horarios->departure);
            
            if((!$extraHours) && ($horarios->name == 'Tarde' || $horarios->name == 'Noche')){
                $horaExtra = floor($horarios->overtimepershift);
                $minutoExtra = ($horarios->overtimepershift - $horaExtra) > 0 ? (60/(1/($horarios->overtimepershift - $horaExtra))) : 0;
                $horaSalida->subHour((Integer)$horaExtra);
                $horaSalida->subMinute((Integer)$minutoExtra);
            }
            
            for ($i=0; $i < 7 ; $i++) { 
                $entrada = Carbon::create($startDate->year, $startDate->month, $startDate->day, (Integer)$horaEntrada->format('H'), (Integer)$horaEntrada->format('i'), 00);
                $salida = Carbon::create($startDate->year, $startDate->month, $startDate->day, (Integer)$horaSalida->format('H'), (Integer)$horaSalida->format('i'), 00);
                $entrada = $entrada->subMinutes(random_int(1, 15))->subSecond(random_int(-59, 0))->format('H:i:s');
                $salida = $salida->subMinutes(random_int(-11, -1))->subSecond(random_int(-59, 0))->format('H:i:s');
                $hasIncident = $incidents->where('start_date','<=',$startDate->format('Y-m-d'))->where('end_date','>=',$startDate->format('Y-m-d'));
                $hasHoliday = $holidays->where('fecha',$startDate->format('Y-m-d'));
                if(($startDate->dayOfWeek != $request->descanso) && $hasIncident->isEmpty() && $hasHoliday->isEmpty()){
                    if($horarios->is_night == 0){
                        array_push($dias, array('turno' => $horarios->name, 'horario' => $horarios->entry.' / '.$horarios->departure, 'fecha_entrada' => $startDate->format('d-m-Y'), 'entrada' => $entrada, 'fecha_salida' => $startDate->format('d-m-Y'), 'salida' => $salida));
                    }else{
                        array_push($dias, array('turno' => $horarios->name, 'horario' => $horarios->entry.' / '.$horarios->departure, 'fecha_entrada' => $startDate->subDay()->format('d-m-Y'), 'entrada' => $entrada, 'fecha_salida' => $startDate->addDay()->format('d-m-Y'), 'salida' => $salida));
                    }
                }else{
                    array_push($dias, array('turno' => $horarios->name, 'horario' => $horarios->entry.' / '.$horarios->departure, 'fecha_entrada' => $startDate->format('d-m-Y'), 'entrada' => '', 'fecha_salida' => $startDate->format('d-m-Y'), 'salida' => ''));
                }
                $startDate->addDay();
            }
            array_push($semanas,$dias);
            $dias = [];
            $numTurno = $numTurno + 1;
        }

        $registers->employee = $employee;
        $registers->data = $semanas;
        // dd($registers);
        return view('genTurnos.viewGenerated', ['register' => $registers, 'ruta' => route('registro_generate_save')]);
    }

    public function registersGenerateSave(Request $request){
        $actualRegisters = \DB::table('registers')->where('employee_id',$request->register['employee']['id'])->whereBetween('date',[$request->register['startDate'], $request->register['endDate']])->get();
        $Inserts = [];
        
        foreach ($request->register['data'] as $data) {
            foreach($data as $day){
                $fecha_entrada = Carbon::parse($day['fecha_entrada'])->format('Y-m-d');
                $fecha_salida = Carbon::parse($day['fecha_salida'])->format('Y-m-d');
                $entryExist = $actualRegisters->where('date', $fecha_entrada)->where('type_id',1)->first();
                $departureExist = $actualRegisters->where('date', $fecha_salida)->where('type_id',2)->first();
                if(is_null($entryExist) && is_null($departureExist) && ($day['entrada'] != null) && ($day['salida'] != null)){
                    array_push($Inserts, array(
                        'employee_id' => $request->register['employee']['id'],
                        'date' => $fecha_entrada,
                        'time' => $day['entrada'],
                        'type_id' => 1,
                        'type_system' => null,
                        'form_creation_id' => 2,
                        'user_id' => auth()->user()->id,
                        'is_delete' => 0,
                        'is_modified' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'biostar_id' => null,
                        'date_original' => $fecha_entrada,
                        'time_original' => $day['entrada'],
                        'type_original' => 1
                        )
                    );

                    array_push($Inserts, array(
                        'employee_id' => $request->register['employee']['id'],
                        'date' => $fecha_salida,
                        'time' => $day['salida'],
                        'type_id' => 2,
                        'type_system' => null,
                        'form_creation_id' => 2,
                        'user_id' => auth()->user()->id,
                        'is_delete' => 0,
                        'is_modified' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'biostar_id' => null,
                        'date_original' => $fecha_salida,
                        'time_original' => $day['salida'],
                        'type_original' => 2
                        )
                    );
                }
            }
        }

        DB::transaction(function () use ($Inserts) {
            DB::table('registers')->insert($Inserts);
        });

        return response()->json(array('result' => true, 'redirectRoute' => route('registro_index_generate')), 200);
    }
}
