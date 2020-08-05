<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\employees;
use App\Models\job;
use App\Models\way_register;
use App\Models\department;
use App\Models\benefitsPolice;
use App\Models\company;
use App\Models\DepartmentRH;
use DB;

class employeeController extends Controller
{
    private $companies;
    private $rhdepartments;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = employees::where('is_delete','0')->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->job;
            $datas->way_register;
        });
        return view('employee.index', compact('datas'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $job = job::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        
        return view('employee.create')->with('way',$way)
                                        ->with('job',$job)
                                        ->with('benPols',$benPols);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        employees::create($request->all());
        return redirect('employee')->with('mensaje','Empleado fue creado con exito');
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
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $department = department::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');;
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        $data = employees::findOrFail($id);

        return view('employee.edit', compact('data'))
                                        ->with('way',$way)
                                        ->with('department',$department)
                                        ->with('benPols',$benPols);
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
        $employee = employees::findOrFail($id);
        $employee->way_register_id = $request->way_register_id;
        $employee->is_overtime = $request->is_overtime;
        $employee->department_id = $request->department_id;
        if(isset($request->job_id)){
            $employee->job_id = $request->job_id;
        }
        $employee->job_id = 25;
        $employee->ben_pol_id = $request->ben_pol_id;
        $employee->updated_by = session()->get('user_id');
        $employee->save();


        return redirect('employee')->with('mensaje', 'Empleado actualizado con exito');
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
            $employee = employees::find($id);
            $employee->fill($request->all());
            $employee->is_delete = 1;
            $employee->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }

    public function supervisorsView(){
        
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
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active','1')
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','employees.short_name AS shortName','employees.id AS idEmployee','jobs.name AS nameJob','departments.name AS nameDepartment')
                        ->get();
        return view('employee.supervisorsView', compact('employees'));
    }

    public function editShortname ($id) {
        $data = employees::findOrFail($id);
        $data->job;
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
        $departments = DB::table('departments')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->whereIn('departments.dept_group_id',$Adgu)
                        ->select('departments.id AS idDep','departments.name AS nameDep')
                        ->get();
        
        return view('employee.editShortname')->with('data',$data)->with('departments',$departments);    
    }

    public function updateShortname (Request $request, $id){
        $employee = employees::findOrFail($id);
        $employee->short_name = $request->short_name;
        $employee->department_id = $request->department_id;
        $employee->job_id = $request->job_id;
        $employee->updated_by = session()->get('user_id');
        $employee->update();
        return redirect('employee/supervisorsView')->with('mensaje', 'Empleado actualizado con exito');    
    }

    /**
     * Undocumented function
     *
     * @param [type] $lEmployees
     * @return void
     */
    public function saveEmployeesFromJSON($lEmployees)
    {
        $lCapEmployees = employees::select('id', 'external_id')
                                    ->pluck('id', 'external_id');

        $this->companies = company::select('id', 'external_id')
                                ->pluck('id', 'external_id');
        $this->rhdepartments = DepartmentRH::select('id', 'external_id')
                                ->pluck('id', 'external_id');

        foreach ($lEmployees as $jEmployee) {
            try {
                $id = $lCapEmployees[$jEmployee->id_employee];
                $this->updEmployee($jEmployee, $id);
            }
            catch (\Throwable $th) {
                $this->insertEmployee($jEmployee);
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $jEmployee
     * @param [type] $id
     * @return void
     */
    private function updEmployee($jEmployee, $id)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        //$department = DepartmentRH::where('id',$jEmployee->dept_rh_id);
        //if($department->default_dept_id != null){
            //$dept = $department->default_dept_id;
        //}else{
            //$dept = $config->dept_pre;
        //}
        employees::where('id', $id)
                    ->update(
                            [
                            'num_employee' => $jEmployee->num_employee,
                            'name' => $jEmployee->firstname." ".$jEmployee->lastname,
                            'names' => $jEmployee->firstname,
                            'first_name' => $jEmployee->lastname,
                            'admission_date' => $jEmployee->admission_date,
                            'leave_date' => $jEmployee->leave_date,
                            'is_overtime' => $jEmployee->extra_time,
                            'company_id' => $this->companies[$jEmployee->company_id],
                            'dept_rh_id' => $this->rhdepartments[$jEmployee->dept_rh_id],
                            //'department_id' => $dept,
                            'way_pay_id' => $jEmployee->way_pay == 1 ? 2 : 1,
                            'is_active' => $jEmployee->is_active,
                            'is_delete' => $jEmployee->is_deleted,
                            'updated_by' => session()->get('user_id'),
                            ]
                        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $jEmployee
     * @return void
     */
    private function insertEmployee($jEmployee)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $emp = new employees();

        $emp->num_employee = $jEmployee->num_employee;
        $emp->name = $jEmployee->firstname." ".$jEmployee->lastname;
        $emp->names = $jEmployee->firstname;
        $emp->first_name = $jEmployee->lastname;
        $emp->admission_date = $jEmployee->admission_date;
        $emp->leave_date = $jEmployee->leave_date;
        $emp->nip = 0;
        $emp->is_overtime = $jEmployee->extra_time;
        $emp->way_register_id = 2; // pendiente
        $emp->ben_pol_id = 1; // estricto
        $emp->job_id = 25; // ???
        $emp->external_id = $jEmployee->id_employee;
        $emp->company_id = $this->companies[$jEmployee->company_id];
        $emp->dept_rh_id = $this->rhdepartments[$jEmployee->dept_rh_id];
        $department = DepartmentRH::where('id',$jEmployee->dept_rh_id)->get();
        if($department[0]->default_dept_id != null){
            $emp->department_id = $department[0]->default_dept_id;
        }else{
            $emp->department_id = $config->dept_pre;
        }
        $emp->is_config = 0;
        $emp->way_pay_id = $jEmployee->way_pay == 1 ? 2 : 1;
        $emp->is_active = $jEmployee->is_active;
        $emp->is_delete = $jEmployee->is_deleted;
        $emp->created_by = session()->get('user_id');
        $emp->updated_by = session()->get('user_id');
        $emp->save();
    }

    public function fingerprints(){
        $employees = DB::table('employees')
                        ->leftjoin('fingerprints','employees.id','=','fingerprints.employee_id')
                        ->join('way_register','way_register.id','=','employees.way_register_id')
                        ->groupBy('employees.id')
                        ->orderBy('employees.name')
                        ->where('employees.is_delete','0')
                        ->where('employees.is_active', '1')
                        ->select('employees.id AS idEmployee','employees.name AS nameEmployee','way_register.name AS way','fingerprints.id AS fingerprint','employees.num_employee AS num')
                        ->get(); 
        
        return view('employee.fingerprints')->with('employees',$employees); 
    }
    public function fingerprintsDisable(){
        $employees = DB::table('employees')
                        ->leftjoin('fingerprints','employees.id','=','fingerprints.employee_id')
                        ->join('way_register','way_register.id','=','employees.way_register_id')
                        ->groupBy('employees.id')
                        ->orderBy('employees.name')
                        ->where('employees.is_delete','2')
                        ->where('employees.is_active', '1')
                        ->select('employees.id AS idEmployee','employees.name AS nameEmployee','way_register.name AS way','fingerprints.id AS fingerprint','employees.num_employee AS num')
                        ->get(); 
        
        return view('employee.fingerprintDisable')->with('employees',$employees); 
    }

    public function fingerprintEdit($id){
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $data = employees::findOrFail($id);

        return view('employee.fingerprintedit', compact('data'))
                                        ->with('way',$way);   
    }

    public function Editfingerprint(Request $request, $id){

        $actualizar = employees::findOrFail($id);
        $actualizar->way_register_id = $request->way_register_id;
        $actualizar->updated_by = session()->get('user_id');
        $actualizar->save();
        return redirect('employee')->with('mensaje', 'Empleado actualizado con exito');
    }

    public function desactivar(Request $request,$id){
        if ($request->ajax()) {
            $employee = employees::find($id);
            $employee->fill($request->all());
            $employee->is_delete = 2;
            $employee->updated_by = session()->get('user_id');
            $employee->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }
    public function activar(Request $request,$id){
        if ($request->ajax()) {
            $employee = employees::find($id);
            $employee->fill($request->all());
            $employee->is_delete = 0;
            $employee->updated_by = session()->get('user_id');
            $employee->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }

    public function indexBenefits(Request $request)
    {
        $lEmployees = DB::table('employees e')
                            ->join('benefit_policies bp', 'e.ben_pol_id','=','bp.id')
                            ->where('e.is_delete', false)
                            ->where('e.is_active', true)
                            ->select(['name', 'id', 'num_employee', 'ben_pol_id', 'bp.name AS bp_name'])
                            ->get();

        return view('employee.employeebenefits')
                    ->with('lEmployees', $lEmployees)
                    ->with('sTitle', "Política de beneficios");
    }

    public function outstandingemployees(){
        $datas = employees::where('is_delete','0')->where('is_config', 0)->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->job;
            $datas->department;
        });
        return view('employee.outstandingemp', compact('datas'));    
    }

    public function editoutstanding($id){
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $departments = department::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        $data = employees::findOrFail($id);

        return view('employee.editoutstanding', compact('data'))
                                        ->with('way',$way)
                                        ->with('department',$departments)
                                        ->with('benPols',$benPols);    
    }
    public function updateoutstanding(Request $request, $id)
    {
        $employee = employees::findOrFail($id);
        $employee->way_register_id = $request->way_register_id;
        $employee->is_overtime = $request->is_overtime;
        $employee->department_id = $request->department_id;
        if(isset($request->job_id)){
            $employee->job_id = $request->job_id;
        }
        $employee->job_id = 25;
        $employee->ben_pol_id = $request->ben_pol_id;
        $employee->updated_by = session()->get('user_id');
        $employee->is_config = 1;
        $employee->save();


        return redirect('employee/outstanding')->with('mensaje', 'Empleado actualizado con exito');
    }
    public function jobs(Request $request){
        $pertenece = 0;
        $numero = session()->get('name');
        $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
        $dgu = DB::table('group_dept_user')
                    ->where('user_id',$usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
        $Adgu = [];
        $grupo = DB::table('departments')
                    ->where('id',$request->departamento)
                    ->get();

        if(isset($dgu[0])){
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }
            for($i = 0; count($Adgu) > $i ; $i++){
                if($Adgu[$i] == $grupo[0]->dept_group_id){
                    $pertenece = 1;
                }
            }
        }
        $departments = DB::table('jobs')
                        ->where('is_delete','0')
                        ->where('department_id',$request->departamento)
                        ->select('jobs.id AS idJob','jobs.name AS nameJob')
                        ->get();
        
        return response()->json(array($departments,$pertenece));

    }
    public function enviarForaneos(Request $request,$id){
        if ($request->ajax()) {
            $config = \App\SUtils\SConfiguration::getConfigurations();
            $employee = employees::findOrFail($id);
            $employee->department_id = $config->dept_foraneo;
            $employee->job_id = $config->job_foraneo;
            $employee->updated_by = session()->get('user_id');
            $employee->is_config = 1;
            $employee->save();
        
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
    public function confirmarConfiguracion(Request $request,$id){
        if ($request->ajax()) {
            $employee = employees::findOrFail($id);
            $employee->is_config = 1;
            $employee->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
    public function foraneos(){
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $datas = employees::where('is_delete','0')->where('department_id', $config->dept_foraneo)->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->job;
            $datas->department;
        });
        return view('employee.outstandingemp', compact('datas'))->with('foraneos',1);   
    }
}

