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
use App\Models\specialworkshift;
use DateTime;
use DB;
use Carbon\Carbon;
use PDF;
use App\Models\prepayrollAdjust;
use App\Models\adjust_link;


class specialWorkshiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $filterType = "2";
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
            $datas = DB::table('specialworkshift')
                        ->join('employees','employees.id','=','specialworkshift.employee_id')
                        ->join('workshifts','workshifts.id','=','specialworkshift.workshift_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('employees.is_delete',0)
                        ->where('specialworkshift.is_delete',0)
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->orderBy('employees.name')
                        ->select('specialworkshift.dateI AS datei','specialworkshift.dateS AS dates','employees.name AS nameEmp','workshifts.name AS nameWork','specialworkshift.is_approved AS is_approved','specialworkshift.id AS id');
        }else{
            $datas = DB::table('specialworkshift')
                        ->join('employees','employees.id','=','specialworkshift.employee_id')
                        ->join('workshifts','workshifts.id','=','specialworkshift.workshift_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('employees.is_delete',0)
                        ->where('specialworkshift.is_delete',0)
                        ->orderBy('employees.name')
                        ->select('specialworkshift.dateI AS datei','specialworkshift.dateS AS dates','employees.name AS nameEmp','workshifts.name AS nameWork','specialworkshift.is_approved AS is_approved' ,'specialworkshift.id AS id');   
        }

        if ($filterType != "2") {
            $datas = $datas->whereBetween('specialworkshift.dateI', [$start_date, $end_date]);
        }
        $datas = $datas->get();

        return view('specialworkshift.index')
                                ->with('datas',$datas)
                                ->with('start_date', $start_date)
                                ->with('end_date', $end_date)
                                ->with('filterType', $filterType);

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

            $datas = DB::table('specialworkshift')
                        ->join('employees','employees.id','=','specialworkshift.employee_id')
                        ->join('workshifts','workshifts.id','=','specialworkshift.workshift_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('employees.is_delete',0)
                        ->where('specialworkshift.is_delete',0)
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->orderBy('employees.name')
                        ->select('specialworkshift.dateI AS datei','specialworkshift.dateS AS dates','employees.name AS nameEmp','workshifts.name AS nameWork','specialworkshift.is_approved AS is_approved','specialworkshift.id AS id');
        }else{
            $datas = DB::table('specialworkshift')
                        ->join('employees','employees.id','=','specialworkshift.employee_id')
                        ->join('workshifts','workshifts.id','=','specialworkshift.workshift_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->where('employees.is_delete',0)
                        ->where('specialworkshift.is_delete',0)
                        ->orderBy('employees.name')
                        ->select('specialworkshift.dateI AS datei','specialworkshift.dateS AS dates','employees.name AS nameEmp','workshifts.name AS nameWork','specialworkshift.is_approved AS is_approved','specialworkshift.id AS id');
        }

        if ($filterType != "2") {
            $datas = $datas->whereBetween('specialworkshift.dateI', [$start_date, $end_date]);
        }

        if ($filterType > "0") {
            if ($filterType == "1") {
                $datas = $datas->where('specialworkshift.is_approved', true);
            }
            else {
                $datas = $datas->where('specialworkshift.is_approved', false);
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

        $lComments = \DB::table('comments')->where('is_delete', 0)->get();


        return view('specialworkshift.create')->with('employees',$employees)->with('workshifts',$workshifts)->with('lComments', $lComments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }
        if($request->employee_id == 0){
            return redirect()->back()->withErrors('Debe seleccionar un empleado');
        }
        if($request->workshift_id == 0){
            return redirect()->back()->withErrors('Debe seleccionar un turno');
        }
        $dateI = Carbon::parse($request->datei);
        $dateS = Carbon::parse($request->dates);

        $cerrado = DB::table('prepayroll_control');


        //return \Redirect::back()->withErrors(['Error', 'La fecha de inicio debe ser previa a la fecha final']);
        $diferencia = ($dateI->diffInDays($dateS));

        $special = new specialworkshift();
        $special->employee_id = $request->employee_id;
        $special->dateI = $request->datei;
        $special->dateS = $request->dates;
        $special->workshift_id = $request->workshift_id;
        $special->is_approved = 1;
        $special->updated_by = session()->get('user_id');
        $special->created_by = session()->get('user_id');
        $special->is_delete = 0;
        $special->save();

        

        for($i = 0 ; $diferencia >= $i ; $i++){
            $week_department_day = new week_department_day();
            $week_department_day->date = $dateI->toDateString();
            $week_department_day->week_department_id = null;
            $week_department_day->status = 1;
            $week_department_day->special = $special->id;
            $week_department_day->is_approved = 1;
            $week_department_day->save();

            $day_workshifts = new day_workshifts();
            $day_workshifts->name = 'na';
            $day_workshifts->day_id = $week_department_day->id;
            $day_workshifts->workshift_id = $request->workshift_id;
            $day_workshifts->is_delete = 0;
            $day_workshifts->save();

            $day_workshifts_employee = new day_workshifts_employee();
            $day_workshifts_employee->employee_id = $request->employee_id;
            $day_workshifts_employee->day_id = $day_workshifts->id;
            $day_workshifts_employee->job_id = null;   
            $day_workshifts_employee->is_rest = 0;
            $day_workshifts_employee->type_day_id = 1;
            $day_workshifts_employee->is_delete = 0; 
            $day_workshifts_employee->save();
            

            $adjust = new prepayrollAdjust();
            $adjust->employee_id = $request->employee_id;
            $adjust->dt_date = $dateI->toDateString();
            $adjust->minutes = 0;
            $adjust->apply_to = 2;
            $adjust->comments = $request->comentarios;
            $adjust->is_delete = 0;
            $adjust->adjust_type_id = 7;
            $adjust->apply_time = 0;
            $adjust->created_by = session()->get('user_id');
            $adjust->updated_by = session()->get('user_id');
            $adjust->save();

            $link = new adjust_link();
            $link->adjust_id = $adjust->id;
            $link->is_special = 1;
            $link->special_id = $special->id;
            $link->save();

            $dateI->addDay();
        }
        
        $special->adjust_id = $adjust->id;
        $special->save(); 
         

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
        $datas = DB::table('specialworkshift')->where('id','=',$id)->get();
                        
        
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

        $lComments = \DB::table('comments')->where('is_delete', 0)->get();

        $adjust = \DB::table('adjust_link')
                    ->where('special_id',$datas[0]->id)
                    ->get();

        $comment_adjust = \DB::table('prepayroll_adjusts')->where('id',$adjust[0]->adjust_id)->get();
        
        return view('specialworkshift.edit')->with('datas',$datas)->with('employees',$employees)->with('workshifts',$workshifts)->with('lComments', $lComments)->with('comment_adjust',$comment_adjust);

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
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }
        if($request->employee_id == 0){
            return redirect()->back()->withErrors('Debe seleccionar un empleado');
        }
        if($request->workshift_id == 0){
            return redirect()->back()->withErrors('Debe seleccionar un turno');
        }
        $specialworkshift = specialworkshift::findOrFail($id);

        $dateI = Carbon::parse($request->datei);
        $dateS = Carbon::parse($request->dates);
        $diferencia = ($dateI->diffInDays($dateS));

        $specialworkshift->employee_id = $request->employee_id;
        $specialworkshift->dateI = $request->datei;
        $specialworkshift->dateS = $request->dates;
        $specialworkshift->workshift_id = $request->workshift_id;
        $specialworkshift->updated_by = session()->get('user_id');
        $specialworkshift->is_delete = 0;
        $specialworkshift->save();

        $week_department_day = DB::table('week_department_day')->where('special','=',$id)->delete();

        //sacar ajustes que tendran que borrarse

        $adjust_delete = DB::table('adjust_link')
        ->where('special_id',$id)
        ->get();

        //$adjust_delete->toArray();

        for( $i = 0 ; count($adjust_delete) > $i ; $i++ ){
            $delete = prepayrollAdjust::where('id',$adjust_delete[$i]->adjust_id)->get();
            $delete[0]->is_delete = 1;
            $delete[0]->save();
        }

        $adjust_delete = DB::table('adjust_link')
            ->where('special_id',$id)
            ->delete();

        for($i = 0 ; $diferencia >= $i ; $i++){
            $week_department_day = new week_department_day();
            $week_department_day->date = $dateI->toDateString();
            $week_department_day->week_department_id = null;
            $week_department_day->status = 1;
            $week_department_day->special = $id;
            $week_department_day->is_approved = 1;
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
            $dateI->addDay();

            $adjust = new prepayrollAdjust();
            $adjust->employee_id = $request->employee_id;
            $adjust->dt_date = $dateI->toDateString();
            $adjust->minutes = 0;
            $adjust->apply_to = 2;
            $adjust->comments = $request->comentarios;
            $adjust->is_delete = 0;
            $adjust->adjust_type_id = 7;
            $adjust->apply_time = 0;
            $adjust->created_by = session()->get('user_id');
            $adjust->updated_by = session()->get('user_id');
            $adjust->save();

            $link = new adjust_link();
            $link->adjust_id = $adjust->id;
            $link->is_special = 1;
            $link->special_id = $id;
            $link->save();
        }
    


         

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
        $specialworkshift = specialworkshift::findOrFail($id);
        $specialworkshift->is_approved =  $specialworkshift->is_approved ? false : true;
        $specialworkshift->updated_by = session()->get('user_id');
        $specialworkshift->save();

        $week_department_day = DB::table('week_department_day')->where('special','=',$id)->get();
        for($i = 0 ; count($week_department_day) > $i ; $i++){
            if($week_department_day[$i]->is_approved == 0){
                DB::table('week_department_day')->where('id','=',$week_department_day[$i]->id)->update(['is_approved'=>1]);    
            }else{
                DB::table('week_department_day')->where('id','=',$week_department_day[$i]->id)->update(['is_approved'=>0]);    
            }
        }
        

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
            $special = specialworkshift::findOrFail($id);
            $special->is_approved = session()->get('user_id');
            $special->is_delete = 1;
            $special->save();
            
            //sacar ajustes que tendran que borrarse

            $adjust_delete = DB::table('adjust_link')
                    ->where('special_id',$special->id)
                    ->get();
            
            //$adjust_delete->toArray();

            for( $i = 0 ; count($adjust_delete) > $i ; $i++ ){
                $delete = prepayrollAdjust::where('id',$adjust_delete[$i]->adjust_id)->get();
                $delete[0]->is_delete = 1;
                $delete[0]->save();
            }

            $adjust_delete = DB::table('adjust_link')
                    ->where('special_id',$special->id)
                    ->delete();
            
            $week_department_day = DB::table('week_department_day')->where('special','=',$id)->delete();
            
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
