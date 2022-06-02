<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class employeesAssignsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grupos = DB::table('department_group')
                    ->where('is_delete',0)
                    ->get();

        $departments = DB::table('departments')
                        ->where('is_delete',0)
                        ->get();

        $supervisores = DB::table('group_dept_user')
                    ->join('users','group_dept_user.user_id','=','users.id')
                    ->where('group_dept_user.is_delete',0)
                    ->orderBy('users.id')
                    ->select('users.name AS name','users.id AS id')
                    ->groupBy('id')
                    ->get();
        
        return view('employeesAssigns.index',['grupos' => $grupos, 'departments' => $departments, 'supervisores' => $supervisores]);
    }

    public function generateEmployeesAssigns(Request $request){
        switch ($request->tipo) {
            case 1:
                $grupo = DB::table('department_group')
                    ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                    ->leftJoin('employees as e', 'e.department_id','=','d.id')
                    ->where('department_group.is_delete',0)
                    ->where('department_group.id',$request->deptGrp)
                    ->where('d.is_delete',0)
                    ->where([['e.is_delete',0],['e.is_active',1]])
                    ->select('department_group.name AS dg','d.name as dept_name','e.name as employee')
                    ->get();
                $supervisores = DB::table('group_dept_user')
                    ->join('users','group_dept_user.user_id','=','users.id')
                    ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                    ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                    ->where('department_group.id',$request->deptGrp)
                    ->where('group_dept_user.is_delete',0)
                    ->where('department_group.is_delete',0)
                    ->where('d.is_delete',0)
                    ->select('users.name')
                    ->groupBy('users.name')
                    ->get();
                break;
            case 2:
                $grupo = DB::table('department_group')
                    ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                    ->leftJoin('employees as e', 'e.department_id','=','d.id')
                    ->where('department_group.is_delete',0)
                    ->where('d.id',$request->dept)
                    ->where('d.is_delete',0)
                    ->where([['e.is_delete',0],['e.is_active',1]])
                    ->select('department_group.name AS dg','d.name as dept_name','e.name as employee')
                    ->get();
                $supervisores = DB::table('group_dept_user')
                    ->join('users','group_dept_user.user_id','=','users.id')
                    ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                    ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                    ->where('d.id',$request->dept)
                    ->where('group_dept_user.is_delete',0)
                    ->where('department_group.is_delete',0)
                    ->where('d.is_delete',0)
                    ->select('users.name')
                    ->groupBy('users.name')
                    ->get();
                break;
            case 3:
                $grupo = DB::table('group_dept_user')
                    ->join('users','group_dept_user.user_id','=','users.id')
                    ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                    ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                    ->leftJoin('employees as e', 'e.department_id','=','d.id')
                    ->where('users.id',$request->supervisor)
                    ->where('group_dept_user.is_delete',0)
                    ->where('department_group.is_delete',0)
                    ->where('d.is_delete',0)
                    ->where([['e.is_delete',0],['e.is_active',1]])
                    ->select('department_group.name AS dg','d.name as dept_name','e.name as employee', 'users.name')
                    ->get();
                $supervisores = DB::table('group_dept_user')
                    ->join('users','group_dept_user.user_id','=','users.id')
                    ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                    ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                    ->where('users.id',$request->supervisor)
                    ->where('group_dept_user.is_delete',0)
                    ->where('department_group.is_delete',0)
                    ->where('d.is_delete',0)
                    ->select('users.name')
                    ->groupBy('users.name')
                    ->get();
                break;
            
            default:
                $grupo = null;
                $supervisores = null;
                break;
        }

        $sSup = "";
        foreach($supervisores as $sup){
            $sSup = $sup->name.', '.$sSup;
        }

        foreach($grupo as $gr){
            $gr->supervisores = $sSup;
        }

        $route = route('empl_group_assign');
        return view('employeesAssigns.generated',['grupo' => $grupo, 'supervisores' => $supervisores, 'tipo' => $request->tipo, 'route' => $route]);
    }
}
