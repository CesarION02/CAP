<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\department;
use App\Models\area;
use App\Models\DepartmentRH;
use App\Models\employees;
use App\Models\job;
use App\Models\policyHoliday;
use DB;

class departmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $iFilter = $request->ifilter == 0 ? 1 : $request->ifilter;
        $id = session()->get('user_id');
        if (!in_array(session()->get('rol_id'), $config->rolesCanSeeAll)) {
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$id)
                    ->select('groupdept_id AS id')
                    ->get();
            $Adgu = [];
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }
            switch ($iFilter) {
                case 1:
                    $datas = department::where('is_delete','0')->whereIn('departments.dept_group_id',$Adgu)->orderBy('name', 'ASC')->get();
                    $datas->each(function($datas){
                        $datas->area;
                        $datas->rh;
                        $datas->boss;
                        $datas->policyHoliday;
                    });
                    break;
                case 2:
                    $datas = department::where('is_delete','1')->whereIn('departments.dept_group_id',$Adgu)->orderBy('name', 'ASC')->get();
                    $datas->each(function($datas){
                        $datas->area;
                        $datas->rh;
                        $datas->boss;
                        $datas->policyHoliday;
                    });
                    break;
                
                default:
                    $datas = department::orderBy('name')->whereIn('departments.dept_group_id',$Adgu)->get();
                    $datas->each(function($datas){
                        $datas->area;
                        $datas->rh;
                        $datas->boss;
                        $datas->policyHoliday;
                    });
                    break;
            }
        }else{
            switch ($iFilter) {
                case 1:
                    $datas = department::where('is_delete','0')->orderBy('name', 'ASC')->get();
                    $datas->each(function($datas){
                        $datas->area;
                        $datas->rh;
                        $datas->boss;
                        $datas->policyHoliday;
                    });
                    break;
                case 2:
                    $datas = department::where('is_delete','1')->orderBy('name', 'ASC')->get();
                    $datas->each(function($datas){
                        $datas->area;
                        $datas->rh;
                        $datas->boss;
                        $datas->policyHoliday;
                    });
                    break;
                
                default:
                    $datas = department::orderBy('name')->get();
                    $datas->each(function($datas){
                        $datas->area;
                        $datas->rh;
                        $datas->boss;
                        $datas->policyHoliday;
                    });
                    break;
            }   
        }

        
        return view('department.index', compact('datas'))->with('iFilter',$iFilter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $area = area::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $deptrhs = DepartmentRH::where('is_delete',0)->orderBy('name', 'ASC')->pluck('id','name');
        $employees = employees::where('is_active','1')->orderBy('name','ASC')->pluck('id','name');
        $policyh = policyHoliday::orderBy('id','ASC')->pluck('id','name');
        
        $id = session()->get('user_id');
        $Adgu = [];
        if (!in_array(session()->get('rol_id'), $config->rolesCanSeeAll)) {
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$id)
                    ->select('groupdept_id AS id')
                    ->get();
            $Adgu = [];
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }

            $groupDept = DB::table('department_group')
                        ->where('is_delete','0')
                        ->whereIn('id',$Adgu)
                        ->get();
        }else{
            $groupDept = DB::table('department_group')
                        ->where('is_delete','0')
                        ->get();
        }

        return view('department.create')->with('areas',$area)->with('deptrhs',$deptrhs)->with('employees',$employees)->with('policyh',$policyh)->with('groupDept', $groupDept);
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
            if(is_null($elem))
            {
                //return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }
        $department = new department();
        $department->name = $request->name;
        $department->rh_department_id = $request->rh_department_id;
        $department->boss_id = $request->boss_id;
        $department->policy_holiday_id = $request->policy_holiday_id;
        $department->dept_group_id = $request->group_dept_id;
        $department->area_id = $request->area_id;
        $department->updated_by = session()->get('user_id');
        $department->created_by = session()->get('user_id');
        $department->save();


        for( $i = 1 ; $request->contador >= $i ; $i++ ){
            $job = new job();
            
            $puesto = 'puesto'.$i;
            
            $job->name  = $request->$puesto;
            $job->department_id = $department->id;
            $job->is_delete = 0;
            $job->created_by = session()->get('user_id');
            $job->updated_by = session()->get('user_id');
            
            $job->save();
        }
        
        

        return redirect('department')->with('mensaje','Departamento fue creado con éxito');
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
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $area = area::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $data = department::findOrFail($id);
        $deptrhs = DepartmentRH::where('is_delete',0)->orderBy('name', 'ASC')->pluck('id','name');
        $employees = employees::where('is_active','1')->orderBy('name','ASC')->pluck('id','name');
        $policyh = policyHoliday::orderBy('id','ASC')->pluck('id','name');

        $id = session()->get('user_id');
        $Adgu = [];
        if (!in_array(session()->get('rol_id'), $config->rolesCanSeeAll)) {
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$id)
                    ->select('groupdept_id AS id')
                    ->get();
            $Adgu = [];
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }

            $groupDept = DB::table('department_group')
                        ->where('is_delete','0')
                        ->whereIn('id',$Adgu)
                        ->get();
        }else{
            $groupDept = DB::table('department_group')
                        ->where('is_delete','0')
                        ->get();
        }

        return view('department.edit', compact('data'))->with('areas',$area)->with('deptrhs',$deptrhs)->with('employees',$employees)->with('policyh',$policyh)->with('groupDept', $groupDept);
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
        $department = department::findOrFail($id);
        $department->rh_department_id = $request->rh_department_id;
        $department->boss_id = $request->boss_id;
        $department->policy_holiday_id = $request->policy_holiday_id;
        $department->dept_group_id = $request->group_dept_id;
        $department->updated_by = session()->get('user_id');
        $department->update($request->all());
        return redirect('department')->with('mensaje', 'Departamento actualizado con éxito');
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
            $department = department::find($id);
            $department->fill($request->all());
            $department->is_delete = 1;
            $department->updated_by = session()->get('user_id');
            $department->save();

            $job = DB::table('jobs')
                        ->where('department_id',$id)
                        ->get();

            for($i = 0 ; count($job) > $i ; $i++){
                $job->is_delete = 1;
                $job->save();
            }
            
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
    public function activar(Request $request,$id){
        if ($request->ajax()) {
            $department = department::find($id);
            $department->fill($request->all());
            $department->is_delete = 0;
            $department->updated_by = session()->get('user_id');
            $department->save();

            $job = DB::table('jobs')
                        ->where('department_id',$id)
                        ->get();

            for($i = 0 ; count($job) > $i ; $i++){
                $job->is_delete = 1;
                $job->save();
            }
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function updateDepts(Request $request)
    {
        $lDepartments = json_decode($request->departments);
        // $deptIds = array();
        foreach ($lDepartments as $dept) {
            department::where('id', $dept->id)
                        ->update(['dept_group_id' => $dept->dept_group_id]);
            // $deptIds[] = $dept->id;
        }

        $lResp = department::where('is_delete', false)
                    ->select('id', 'name', 'dept_group_id')
                    ->orderBy('name', 'ASC')
                    ->get();

        return json_encode($lResp);
    }
}
