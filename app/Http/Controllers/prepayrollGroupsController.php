<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\prepayrollGroupEmployee;

class prepayrollGroupsController extends Controller
{
    public function employeesVsGroups(Request $request)
    {
        $lEmployees = \DB::table('employees AS e')
                            ->leftJoin('prepayroll_group_employees AS pge', 'e.id', '=', 'pge.employee_id')
                            ->leftJoin('prepayroll_groups AS pg', 'pge.group_id', '=', 'pg.id_group')
                            ->leftJoin('users AS u', 'pg.head_user_id', '=', 'u.id')
                            ->select('e.*', 'pge.*', 'pg.*', 'u.name AS gr_titular')
                            // ->where(function ($query) {
                            //     $query->whereNull('pge.is_delete')
                            //             ->orWhere('pge.is_delete', 0);
                            // })
                            ->where('e.is_delete', 0)
                            ->where('e.is_active', 1)
                            ->get();

        $groups = \DB::table('prepayroll_groups')
                            ->select('id_group', 'group_code', 'group_name')
                            ->where('is_delete', 0)
                            ->get();

        return view('prepayroll.indexgr')->with('lEmployees', $lEmployees)
                                            ->with('groups', $groups);
    }

    public function changeGroup(Request $request) {
        \DB::table('prepayroll_group_employees')
                ->where('employee_id', $request->emp_id)
                ->delete();

        if ($request->new_group > 0) {
            $obj = new prepayrollGroupEmployee();
    
            $obj->group_id = $request->new_group;
            $obj->employee_id = $request->emp_id;
            $obj->is_delete = 0;
            $obj->created_by = \Auth::user()->id;
            $obj->updated_by = \Auth::user()->id;
    
            $obj->save();
        }

        return redirect()->route('gr_emps_index')->with('mensaje', 'Grupo de prenómina actualizado.');
    }
}
