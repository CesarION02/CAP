<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\employees;
use App\Models\job;
use App\Models\way_register;
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
        $job = job::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        $data = employees::findOrFail($id);

        return view('employee.edit', compact('data'))
                                        ->with('way',$way)
                                        ->with('job',$job)
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
        employees::findOrFail($id)->update($request->all());
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

    public function supervisorsView($id = 1){

        $employees = DB::table('employees')
                        ->join('jobs','jobs.id','=','employees.job_id')
                        ->join('departments','departments.id','=','jobs.department_id')
                        ->join('department_group','department_group.id','=','departments.dept_group_id')
                        ->orderBy('employees.job_id')
                        ->where('employees.is_delete','0')
                        ->where('departments.dept_group_id',$id)
                        ->orderBy('employees.name')
                        ->select('employees.name AS nameEmployee','employees.num_employee AS numEmployee','employees.short_name AS shortName','employees.id AS idEmployee')
                        ->get();
        return view('employee.supervisorsView', compact('employees'));
    }

    public function editShortname ($id) {
        $data = employees::findOrFail($id);
        return view('employee.editShortname')->with('data',$data);    
    }

    public function updateShortname (Request $request, $id){
        $employee = employees::findOrFail($id);
        $employee->short_name = $request->short_name;
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
                            'way_pay_id' => $jEmployee->way_pay == 1 ? 2 : 1,
                            'is_active' => $jEmployee->is_active,
                            'is_delete' => $jEmployee->is_deleted,
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
        $emp = new employees();

        $emp->num_employee = $jEmployee->num_employee;
        $emp->name = $jEmployee->firstname." ".$jEmployee->lastname;
        $emp->names = $jEmployee->firstname;
        $emp->first_name = $jEmployee->lastname;
        $emp->admission_date = $jEmployee->admission_date;
        $emp->leave_date = $jEmployee->leave_date;
        $emp->nip = 0;
        $emp->is_overtime = $jEmployee->extra_time;
        $emp->way_register_id = 1; // pendiente
        $emp->ben_pol_id = 1; // estricto
        $emp->job_id = 1; // ???
        $emp->external_id = $jEmployee->id_employee;
        $emp->company_id = $this->companies[$jEmployee->company_id];
        $emp->dept_rh_id = $this->rhdepartments[$jEmployee->dept_rh_id];
        $emp->way_pay_id = $jEmployee->way_pay == 1 ? 2 : 1;
        $emp->is_active = $jEmployee->is_active;
        $emp->is_delete = $jEmployee->is_deleted;

        $emp->save();
    }
}
