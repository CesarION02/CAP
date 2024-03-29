<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SUtils\SPrepayrollUtils;
use DB;

class prepayRollEmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = null, $bDirect = 0)
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $isAdmin = false;
        foreach(auth()->user()->roles()->get() as $rol){
            $result = in_array($rol->id, $config->rolesCanSeeAll);
            if($result){
                $isAdmin = true;
                break;
            }
        }
        
        if($isAdmin){
            $lUsers = DB::table('users')
                    ->join('prepayroll_groups_users as pru','pru.head_user_id','=','users.id')
                    ->select('users.id','users.name')
                    ->orderBy('users.name')
                    ->get();
        }else{
            $lUsers = DB::table('users')
                        ->join('prepayroll_groups_users as pru','pru.head_user_id','=','users.id')
                        ->where('users.id',auth()->user()->id)
                        ->select('users.id','users.name')
                        ->orderBy('users.name')
                        ->get();

            $id = auth()->user()->id;
        }

        $payType = 0;
        $subEmployees = SPrepayrollUtils::getEmployeesByUser($id, $payType, $bDirect, null);

        $lEmployees = \DB::table('employees AS e')
                            ->leftJoin('prepayroll_group_employees AS pge', 'e.id', '=', 'pge.employee_id')
                            ->leftJoin('prepayroll_groups AS pg', 'pge.group_id', '=', 'pg.id_group')
                            ->leftJoin('prepayroll_groups_users AS pgu', 'pge.group_id', '=', 'pgu.group_id')
                            ->leftJoin('users AS u', 'pgu.head_user_id', '=', 'u.id')
                            ->leftJoin('departments as dep', 'dep.id', '=', 'e.department_id')
                            ->leftJoin('prepayroll_group_deptos AS pgd', 'dep.id', '=', 'pgd.department_id')
                            ->leftJoin('prepayroll_groups AS pgDep', 'pgd.group_id', '=', 'pgDep.id_group')
                            ->select('e.id as employee_id', 'e.num_employee', 'e.name as employee', 'pg.id_group',
                            'pg.group_name as group_name_employee', 'pg.father_group_n_id','u.name AS gr_titular', 'dep.name as department',
                            'pgDep.group_name as group_name_depto')
                            ->whereIn('e.id',$subEmployees)
                            ->where('e.external_id','!=',null)
                            ->groupBy('e.id')
                            ->get();
        
        return view('prepayRollGroupEmployees.index',['lUsers' => $lUsers, 'lEmployees' => $lEmployees, 'idUser' => $id, 'bDirect' => $bDirect]);
    }

    public function generate(Request $request)
    {

        $idUser = $request->user;
        $bDirect = isset($request->bDirect) ? 1 : 0;
        
        return redirect(route('prepayroll_emp_grupo', ['id' => $idUser, 'bDirect' => $bDirect]));
    }

    public function getDirectEmployees(Request $request)
    {
        $dirEmpl = SPrepayrollUtils::getEmployeesByUser($request->id, 0, true, null);
        $subEmployees = [];
        foreach ($dirEmpl as $data) {
            array_push($subEmployees, $data);
        }
        return json_encode($subEmployees);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
