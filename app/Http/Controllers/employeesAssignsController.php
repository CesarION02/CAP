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

    public function indexEmployeesSchedules(Request $request){
        if (session()->get('rol_id') != 1){
            $grupo = DB::table('group_dept_user as gdu')
                        ->join('users as u','gdu.user_id','=','u.id')
                        ->join('department_group as dg','gdu.groupdept_id','=','dg.id')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','dg.id')
                        ->leftJoin('employees as e', 'e.department_id','=','d.id')
                        ->where('u.id', \Auth::user()->id)
                        ->where('gdu.is_delete',0)
                        ->where('dg.is_delete',0)
                        ->where('d.is_delete',0)
                        ->where([['e.is_delete',0],['e.is_active',1]])
                        ->select('u.name as supervisor', 'dg.name as dept_group', 'd.name as dept', 'e.id as employee_id',
                                    'e.num_employee', 'e.name as employee_name',
                                    \DB::raw("CONCAT(d.name,' - ',e.name,' - ',e.num_employee) AS employee"))
                        ->get();
        }else{
            $grupo = DB::table('group_dept_user as gdu')
                        ->join('users as u','gdu.user_id','=','u.id')
                        ->join('department_group as dg','gdu.groupdept_id','=','dg.id')
                        ->leftJoin('departments as d', 'd.dept_group_id','=','dg.id')
                        ->leftJoin('employees as e', 'e.department_id','=','d.id')
                        ->where('gdu.is_delete',0)
                        ->where('dg.is_delete',0)
                        ->where('d.is_delete',0)
                        ->where([['e.is_delete',0],['e.is_active',1]])
                        ->select('u.name as supervisor', 'dg.name as dept_group', 'd.name as dept', 'e.id as employee_id',
                                    'e.num_employee', 'e.name as employee_name',
                                    \DB::raw("CONCAT(d.name,' - ',e.name,' - ',e.num_employee) AS employee"))
                        ->groupBy('e.id')
                        ->get();
        }

        if($request->selEmployee){
            $schedules = \DB::table('schedule_assign AS sa')
                        ->join('schedule_template as st', 'sa.schedule_template_id', '=', 'st.id')
                        ->where('sa.is_delete', 0)
                        ->where('st.id', '!=', 1)
                        ->where('sa.employee_id', $request->selEmployee)
                        ->select('sa.employee_id', 'sa.start_date', 'sa.end_date', 'st.name', 'sa.employee_id', 'sa.id as schedule_id', 'st.id as st_id')
                        ->orderBy('start_date')
                        ->get();

            $schedulesDays = \DB::table('schedule_assign AS sa')
                                ->join('schedule_template as st', 'sa.schedule_template_id', '=', 'st.id')
                                ->join('schedule_day as sd', 'sd.schedule_template_id', '=', 'st.id')
                                ->where('sa.is_delete', 0)
                                ->where('sa.employee_id', $request->selEmployee)
                                ->select('sd.is_active', 'sd.day_name', 'sd.day_num', 'sd.entry as day_entry', 'sd.departure as day_departure', 'sd.schedule_template_id')
                                ->groupBy('sd.schedule_template_id', 'sd.day_name')
                                ->orderBy('sd.schedule_template_id', 'asc')
                                ->orderBy('sd.day_num', 'asc')
                                ->get();

            foreach($schedules as $schedule){
                $schDay = $schedulesDays->where('schedule_template_id',$schedule->st_id)->all();
                $schedule->days = $schDay;
            }

            $oEmployee = $grupo->where('employee_id', $request->selEmployee)->first();
        }else{
            $schedules = [];
            $oEmployee = new \stdClass();
            $oEmployee->employee_id = 0;
        }

        return view('employeesSchedules.index', ['lEmployees' => $grupo, 'lSchedules' => $schedules, 'oEmployee' => $oEmployee]);
    }

    public function deleteScheduleAssign($id){
        $schedule = \DB::table('schedule_assign')
                        ->where('id',$id)
                        ->update(['is_delete' => 1]);

        return json_encode(['success' => $schedule]);
    }
}
