<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\register;
use App\Models\Dateregister;
use Carbon\Carbon;
use DB;

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
                        ->whereBetween('date', [$start_date, $end_date])
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','registers.id AS id','registers.date AS date','registers.time AS time','registers.type_id AS tipo','users.name AS usuario')
                        ->get();   
        }
        return view('register.index', compact('employees'))
                                    ->with('start_date', $start_date)
                                    ->with('end_date', $end_date);
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
                        ->select('employees.name AS nameEmployee','employees.id AS id')
                        ->get();
        }else{
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.id AS id')
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
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.id AS id')
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
        $register->user_id = session()->get('user_id');
        $register->save();

        $iddateregister = Dateregister::where('register_id','=',$id)->get();
        $dateregister = Dateregister::find($iddateregister[0]->id);
        $dateregister->updated_by = session()->get('user_id');
        
        $dateregister->save();


        return redirect('register')->with('mensaje', 'Checador actualizado con éxito');
    }

    public function destroy(Request $request,$id)
    {
        if ($request->ajax()) {
            $employee = register::find($id);
            $employee->is_delete = 1;
            $employee->save();
            $iddateregister = Dateregister::where('register_id','=',$id)->get();
            $dateregister = Dateregister::find($iddateregister[0]->id);
            $dateregister->is_delete = 1;
            $dateregister->save();

            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
