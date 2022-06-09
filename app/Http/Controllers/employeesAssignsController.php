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
                if($request->deptGrp != 0){
                    $grupo = DB::table('department_group')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->leftJoin('employees as e', 'e.department_id','=','d.id')
                        ->where('department_group.is_delete',0)
                        ->where('department_group.id',$request->deptGrp)
                        ->where('d.is_delete',0)
                        ->where([['e.is_delete',0],['e.is_active',1]])
                        ->select('department_group.name AS dg','d.name as dept_name','e.name as employee', 'department_group.id')
                        ->get();
                    $supervisores = DB::table('group_dept_user')
                        ->join('users','group_dept_user.user_id','=','users.id')
                        ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->where('department_group.id',$request->deptGrp)
                        ->where('group_dept_user.is_delete',0)
                        ->where('department_group.is_delete',0)
                        ->where('d.is_delete',0)
                        ->select('users.name', 'group_dept_user.groupdept_id')
                        ->groupBy('users.name')
                        ->get();
                }else{
                    $grupo = DB::table('department_group')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->leftJoin('employees as e', 'e.department_id','=','d.id')
                        ->where('department_group.is_delete',0)
                        ->where('d.is_delete',0)
                        ->where([['e.is_delete',0],['e.is_active',1]])
                        ->select('department_group.name AS dg','d.name as dept_name','e.name as employee', 'department_group.id')
                        ->get();

                    $supervisores = DB::table('group_dept_user as gdu')
                                        ->join('users','gdu.user_id','=','users.id')
                                        ->select('users.name', 'gdu.groupdept_id')
                                        ->get();
                }
                break;
            case 2:
                if($request->dept != 0){
                    $grupo = DB::table('department_group')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->leftJoin('employees as e', 'e.department_id','=','d.id')
                        ->where('department_group.is_delete',0)
                        ->where('d.id',$request->dept)
                        ->where('d.is_delete',0)
                        ->where([['e.is_delete',0],['e.is_active',1]])
                        ->select('department_group.name AS dg','d.name as dept_name','e.name as employee', 'department_group.id')
                        ->get();
                    $supervisores = DB::table('group_dept_user')
                        ->join('users','group_dept_user.user_id','=','users.id')
                        ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->where('d.id',$request->dept)
                        ->where('group_dept_user.is_delete',0)
                        ->where('department_group.is_delete',0)
                        ->where('d.is_delete',0)
                        ->select('users.name', 'group_dept_user.groupdept_id')
                        ->groupBy('users.name')
                        ->get();
                }else{
                    $grupo = DB::table('department_group')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->leftJoin('employees as e', 'e.department_id','=','d.id')
                        ->where('department_group.is_delete',0)
                        ->where('d.is_delete',0)
                        ->where([['e.is_delete',0],['e.is_active',1]])
                        ->select('department_group.name AS dg','d.name as dept_name','e.name as employee', 'department_group.id')
                        ->get();

                    $supervisores = DB::table('group_dept_user as gdu')
                                        ->join('users','gdu.user_id','=','users.id')
                                        ->select('users.name', 'gdu.groupdept_id')
                                        ->get();
                }
                break;
            case 3:
                if($request->supervisor != 0){
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
                        ->select('department_group.name AS dg','d.name as dept_name','e.name as employee', 'users.name', 'department_group.id')
                        ->get();
                    $supervisores = DB::table('group_dept_user')
                        ->join('users','group_dept_user.user_id','=','users.id')
                        ->join('department_group','group_dept_user.groupdept_id','=','department_group.id')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->where('users.id',$request->supervisor)
                        ->where('group_dept_user.is_delete',0)
                        ->where('department_group.is_delete',0)
                        ->where('d.is_delete',0)
                        ->select('users.name', 'group_dept_user.groupdept_id')
                        ->groupBy('users.name')
                        ->get();
                }else{
                    $grupo = DB::table('department_group')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','department_group.id')
                        ->leftJoin('employees as e', 'e.department_id','=','d.id')
                        ->where('department_group.is_delete',0)
                        ->where('d.is_delete',0)
                        ->where([['e.is_delete',0],['e.is_active',1]])
                        ->select('department_group.name AS dg','d.name as dept_name','e.name as employee', 'department_group.id')
                        ->get();

                    $supervisores = DB::table('group_dept_user as gdu')
                                        ->join('users','gdu.user_id','=','users.id')
                                        ->select('users.name', 'gdu.groupdept_id')
                                        ->get();
                }
                break;
            
            default:
                $grupo = null;
                $supervisores = null;
                break;
        }

        $sSup = "";
        foreach ($grupo as $gr) {
            foreach($supervisores as $sup){
                if($sup->groupdept_id == $gr->id){
                    $sSup = $sup->name.', '.$sSup;
                }
            }
            $gr->supervisores = $sSup;
            $sSup = "";
        }

        $route = route('empl_group_assign');
        return view('employeesAssigns.generated',['grupo' => $grupo, 'supervisores' => $supervisores, 'tipo' => $request->tipo, 'route' => $route]);
    }
}
