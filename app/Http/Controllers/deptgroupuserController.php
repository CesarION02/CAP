<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departmentsGroup;
use App\Models\group_dept_user;
use App\User;
use DB;

class deptgroupuserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grupo = DB::table('group_dept_user')
                    ->join('users','group_dept_user.user_id','=','users.id')
                    ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                    ->where('group_dept_user.is_delete',0)
                    ->orderBy('users.id')
                    ->select('users.name AS nombreEmpleado','department_group.name AS dg','users.id AS id')
                    ->get();
        $comparacion = 0;
        $departamentos = '';
        $j=0;
        $groupDept = [];
        for($i = 0 ; count($grupo) > $i ; $i++){
            if($comparacion == $grupo[$i]->id){
                if($departamentos == ''){
                    $departamentos = $grupo[$i]->dg;
                }else{
                    $departamentos = $departamentos.', '.$grupo[$i]->dg;
                }
                 
            }else{
                if($comparacion != 0){
                    
                    $groupDept[$j] = $departamentos;
                    $departamentos = '';
                    $j++;
                }
                $comparacion = $grupo[$i]->id;
                $departamentos = $grupo[$i]->dg;
            }
            
        }
        $groupDept[$j] = $departamentos;
        $grupo = DB::table('group_dept_user')
                    ->join('users','group_dept_user.user_id','=','users.id')
                    ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                    ->where('group_dept_user.is_delete',0)
                    ->orderBy('users.id')
                    ->groupBy('users.id')
                    ->select('users.name AS nombreEmpleado','users.id AS id')
                    ->get();

        return view('groupdeptuser.index')->with('departamentos',$groupDept)->with('user',$grupo);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = User::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $department = departmentsGroup::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');

        return view('groupdeptuser.create')->with('users',$user)->with('departments',$department);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $numero = count($request->dgu);
        for($i = 0 ; $numero > $i ; $i++){
            $dgu = new group_dept_user();
            $dgu->user_id = $request->usuario;
            $dgu->groupdept_id = $request->dgu[$i];
            $dgu->created_by = 1;
            $dgu->updated_by = 1;
            $dgu->is_delete = 0;
            $dgu->save();
        }
        return redirect('deptgroupuser')->with('mensaje','Se asigno grupo de departamentos correctamente');
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
        $empleados = DB::table('users')
                        ->where('id',$id)
                        ->select('id AS id','name AS nombre')
                        ->get();
        $department = departmentsGroup::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');

        $datas = DB::table('group_dept_user')
            ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
            ->where('group_dept_user.is_delete',0)
            ->where('group_dept_user.user_id',$id)
            ->select('department_group.id AS dg')
            ->get();

        return view('groupdeptuser.edit')->with('departments',$department)->with('empleados',$empleados)->with('datas',$datas);   
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
        $borrar = group_dept_user::where('user_id',$id)->delete();
        $numero = count($request->dgu);
        for($i = 0 ; $numero > $i ; $i++){
            $dgu = new group_dept_user();
            $dgu->user_id = $id;
            $dgu->groupdept_id = $request->dgu[$i];
            $dgu->created_by = 1;
            $dgu->updated_by = 1;
            $dgu->is_delete = 0;
            $dgu->save();
        }
        return redirect('deptgroupuser')->with('mensaje','Se actualizo grupo de departamentos correctamente');
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
            $registros = DB::table('group_dept_user')
                            ->where('user_id',$id)
                            ->select('id AS id')
                            ->get();
            for($i = 0 ; count($registros) > $i ; $i++){
                $dgu = group_dept_user::find($registros[$i]->id);
                $dgu->is_delete = 1;
                $dgu->save();
                return response()->json(['mensaje' => 'ok']);
            }                
            
        } else {
            abort(404);
        }
    }
}
