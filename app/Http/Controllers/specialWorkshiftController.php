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
use Carbon\Carbon;
use PDF;


class specialWorkshiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
            $datas = DB::table('day_workshifts_employee')
                        ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                        ->join('day_workshifts','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('workshifts','workshifts.id','=','day_workshifts.workshift_id')
                        ->join('week_department_day','week_department_day.id','=','day_workshifts.day_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('week_department_day.date')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('day_workshifts_employee.is_delete',0)
                        ->where('week_department_day.week_department_id',null)
                        ->whereBetween('week_department_day.date', [$start_date, $end_date])
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->orderBy('employees.name')
                        ->select('week_department_day.date AS date',
                                    'employees.name AS nameEmp',
                                    'workshifts.name AS nameWork',
                                    'week_department_day.is_approved',
                                    'day_workshifts_employee.id AS id')
                        ->get();
        }else{
            $datas = DB::table('day_workshifts_employee')
                        ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                        ->join('day_workshifts','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('workshifts','workshifts.id','=','day_workshifts.workshift_id')
                        ->join('week_department_day','week_department_day.id','=','day_workshifts.day_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('week_department_day.date')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('day_workshifts_employee.is_delete',0)
                        ->where('week_department_day.week_department_id',null)
                        ->whereBetween('week_department_day.date', [$start_date, $end_date])
                        ->orderBy('employees.name')
                        ->select('week_department_day.date AS date','employees.name AS nameEmp','workshifts.name AS nameWork','day_workshifts_employee.id AS id','week_department_day.is_approved AS is_approved')
                        ->get();   
        }
        return view('specialworkshift.index')
                                ->with('datas',$datas)
                                ->with('start_date', $start_date)
                                ->with('end_date', $end_date);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexrh(Request $request)
    {   
        $start_date = null;
        $end_date = null;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $start_date = $now->startOfMonth()->toDateString();
            $end_date = $now->endOfMonth()->toDateString();
            $filterType = "2";
        }
        else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $filterType = $request->filter_apprvd;
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
            $datas = DB::table('day_workshifts_employee')
                        ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                        ->join('day_workshifts','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('workshifts','workshifts.id','=','day_workshifts.workshift_id')
                        ->join('week_department_day','week_department_day.id','=','day_workshifts.day_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('week_department_day.date')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('day_workshifts_employee.is_delete',0)
                        ->where('week_department_day.week_department_id',null)
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->orderBy('employees.name')
                        ->select('week_department_day.date AS date',
                                    'employees.name AS nameEmp',
                                    'workshifts.name AS nameWork',
                                    'week_department_day.is_approved',
                                    'day_workshifts_employee.id AS id');
        }else{
            $datas = DB::table('day_workshifts_employee')
                        ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                        ->join('day_workshifts','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('workshifts','workshifts.id','=','day_workshifts.workshift_id')
                        ->join('week_department_day','week_department_day.id','=','day_workshifts.day_id')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('week_department_day.date')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('day_workshifts_employee.is_delete',0)
                        ->where('week_department_day.week_department_id',null)
                        ->orderBy('employees.name')
                        ->select('week_department_day.date AS date','employees.name AS nameEmp','workshifts.name AS nameWork','day_workshifts_employee.id AS id','week_department_day.is_approved AS is_approved');
        }

        if ($filterType != "2") {
            $datas = $datas->whereBetween('week_department_day.date', [$start_date, $end_date]);
        }

        if ($filterType > "0") {
            if ($filterType == "1") {
                $datas = $datas->where('week_department_day.is_approved', true);
            }
            else {
                $datas = $datas->where('week_department_day.is_approved', false);
            }
        }

        $datas = $datas->get();
        
        return view('specialworkshift.indexrh')
                                    ->with('datas',$datas)
                                    ->with('start_date', $start_date)
                                    ->with('end_date', $end_date)
                                    ->with('filterType', $filterType);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
            $workshifts = DB::table('workshifts')
                        ->where('is_delete',0)
                        ->select('name AS name','entry AS entrada', 'departure AS salida','id AS id')
                        ->get();
        }else{
            $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
            $workshifts = DB::table('workshifts')
                        ->where('is_delete',0)
                        ->select('name AS name','entry AS entrada', 'departure AS salida','id AS id')
                        ->get();    
        }
        return view('specialworkshift.create')->with('employees',$employees)->with('workshifts',$workshifts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $week_department_day = new week_department_day();
        $week_department_day->date = $request->date;
        $week_department_day->week_department_id = null;
        $week_department_day->status = 1;
        $week_department_day->save();

        $day_workshifts = new day_workshifts();
        $day_workshifts->name = 'na';
        $day_workshifts->day_id = $week_department_day->id;
        $day_workshifts->workshift_id = $request->workshift_id;
        $day_workshifts->is_delete = 0;
        $day_workshifts->save();

        $day_workshifts_employee = new day_workshifts_employee;
        $day_workshifts_employee->employee_id = $request->employee_id;
        $day_workshifts_employee->day_id = $day_workshifts->id;
        $day_workshifts_employee->job_id = null;   
        $day_workshifts_employee->is_rest = 0;
        $day_workshifts_employee->type_day_id = 1;
        $day_workshifts_employee->is_delete = 0; 
        $day_workshifts_employee->save(); 

        return redirect('specialworkshift')->with('mensaje', 'Turno especial creado con exito');
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
        $datas = DB::table('day_workshifts_employee')
                        ->join('employees','employees.id','=','day_workshifts_employee.employee_id')
                        ->join('day_workshifts','day_workshifts.id','=','day_workshifts_employee.day_id')
                        ->join('workshifts','workshifts.id','=','day_workshifts.workshift_id')
                        ->join('week_department_day','week_department_day.id','=','day_workshifts.day_id')
                        ->where('day_workshifts_employee.id',$id)
                        ->orderBy('employees.name')
                        ->select('employees.id AS idEmployee','employees.name AS nameEmp','workshifts.id AS idWork','week_department_day.date AS date','day_workshifts_employee.id AS id')
                        ->get();
        $workshifts = DB::table('workshifts')
                        ->where('is_delete',0)
                        ->select('name AS name','entry AS entrada', 'departure AS salida','id AS id')
                        ->get();
        
        return view('specialworkshift.edit')->with('datas',$datas)->with('workshifts',$workshifts);

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
        $day_workshift_employee = day_workshifts_employee::findOrFail($id);
        
        $day_workshifts = day_workshifts::findOrFail($day_workshift_employee->day_id);
        $day_workshifts->workshift_id = $request->workshift_id;
        $day_workshifts->save(); 

        $week_department_day = week_department_day::findOrFail($day_workshifts->day_id);
        $week_department_day->date = $request->date;
        $week_department_day->save(); 

        return redirect('specialworkshift')->with('mensaje', 'Turno especial actualizado con exito');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateApproved($id)
    {
        $day_workshift_employee = day_workshifts_employee::findOrFail($id);
        
        $day_workshifts = day_workshifts::findOrFail($day_workshift_employee->day_id);

        $week_department_day = week_department_day::findOrFail($day_workshifts->day_id);
        $week_department_day->is_approved = $week_department_day->is_approved ? false : true;
        $week_department_day->save();

        return redirect('specialworkshiftrh')->with('mensaje', 'Turno especial actualizado con exito');
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
            $day_workshift_employee = day_workshifts_employee::findOrFail($id);
            $day_workshift_employee->is_delete = 1;
            $day_workshift_employee->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
