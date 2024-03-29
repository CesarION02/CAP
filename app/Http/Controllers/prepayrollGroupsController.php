<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\prepayrollGroup;
use App\Models\prepayrollGroupEmployee;
use App\Models\prepayrollGroupDepartment;
use App\Models\UserPPGroup;
use App\SUtils\SPrepayrollUtils;
use Validator;

class prepayrollGroupsController extends Controller
{
    public function index(Request $request)
    {
        $lGroups = \DB::table('prepayroll_groups AS pg')
                        ->leftJoin('prepayroll_groups AS pgf', 'pg.father_group_n_id', '=', 'pgf.id_group')
                        ->where('pg.is_delete', 0)
                        ->select('pg.id_group', 'pg.group_name', 'pg.father_group_n_id', 'pgf.group_name AS father_group_name')
                        ->orderBy('pg.id_group', 'ASC')
                        ->get();

        foreach ($lGroups as $group) {
            $users = \DB::table('prepayroll_groups_users AS pgu')
                            ->join('users AS u', 'pgu.head_user_id', '=', 'u.id')
                            ->where('group_id', $group->id_group)
                            ->pluck('u.name')
                            ->toArray();
            
            $group->head_users = implode(', ', $users);
        }

        return view('prepayroll.groups.index', compact('lGroups'));
    }

    public function create(Request $request)
    {
        $lGroups = prepayrollGroup::where('is_delete', 0)->get();
        
        $oBlank = new prepayrollGroup();
        $oBlank->id_group = null;
        $oBlank->group_name = "NA";

        $lGroups->add($oBlank);

        $lHeadUsers = \DB::table('users AS u')
                        ->select('u.id', 'u.name AS usr_name')
                        ->where('u.is_delete', 0)
                        ->get();

        return view('prepayroll.groups.create', ['lGroups' => $lGroups, 'lHeadUsers' => $lHeadUsers]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_name' => 'required',
        ]);
        $validator->validate();

        if (! SPrepayrollUtils::isValidGroupHeredity(0, $request->father_group_n_id)) {
            return redirect()->back()->withErrors(["Referencia circular en la jerarquía de grupos."]);
        }

        try {
            \DB::beginTransaction();

            

            $oPpGroup = new prepayrollGroup();
            $oPpGroup->group_name = $request->group_name;
            $oPpGroup->father_group_n_id = $request->father_group_n_id;
            $oPpGroup->is_delete = 0;
            $oPpGroup->created_by = \Auth::user()->id;
            $oPpGroup->updated_by = \Auth::user()->id;

            $oPpGroup->save();

            foreach ($request->head_users as $usr_id) {
                $usrGp = new UserPPGroup();

                $usrGp->group_id = $oPpGroup->id_group;
                $usrGp->head_user_id = $usr_id;
                $usrGp->user_by_id = \Auth::user()->id;

                $usrGp->save();
            }

            \DB::commit();
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            return redirect()->back()->withErrors([$th->getMessage(), $request->all()]);
        }

        return redirect()->route('prepayroll_groups')->with('mensaje', 'Grupo de prenómina creado.');
    }

    public function edit(Request $request, $id)
    {
        $oPpGroup = prepayrollGroup::find($id);

        $lGroups = \DB::table('prepayroll_groups AS pg')
                            ->where('pg.is_delete', 0)
                            ->where('pg.id_group', '!=', $id)
                            ->select('id_group', 'group_name')
                            ->get();

        $oBlank = new prepayrollGroup();
        $oBlank->id_group = null;
        $oBlank->group_name = "NA";

        $lGroups->add($oBlank);

        $lHeadUsersSelected = \DB::table('users AS u')
                                    ->join('prepayroll_groups_users AS pgu', 'u.id', '=', 'pgu.head_user_id')
                                    ->select('u.id')
                                    ->where('u.is_delete', 0)
                                    ->where('pgu.group_id', $id)
                                    ->pluck('u.id')
                                    ->toArray();

        $lHeadUsers = \DB::table('users AS u')
                                ->select('u.id', 'u.name AS usr_name')
                                ->where('u.is_delete', 0)
                                ->get();

        return view('prepayroll.groups.edit')->with([
                                                        'oPpGroup' => $oPpGroup,
                                                        'lGroups' => $lGroups,
                                                        'lHeadUsers' => $lHeadUsers,
                                                        'lHeadUsersSelected' => $lHeadUsersSelected
                                                    ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'group_name' => 'required',
        ]);
        $validator->validate();

        if (! SPrepayrollUtils::isValidGroupHeredity($id, $request->father_group_n_id)) {
            return redirect()->back()->withErrors(["Referencia circular en la jerarquía de grupos."]);
        }

        try {
            \DB::beginTransaction();

            $oPpGroup = prepayrollGroup::find($id);

            $oPpGroup->group_name = $request->group_name;
            $oPpGroup->father_group_n_id = $request->father_group_n_id;
            $oPpGroup->updated_by = \Auth::user()->id;

            $oPpGroup->save();

            UserPPGroup::where('group_id', $oPpGroup->id_group)->delete();
            foreach ($request->head_users as $usr_id) {
                $usrGp = new UserPPGroup();

                $usrGp->group_id = $oPpGroup->id_group;
                $usrGp->head_user_id = $usr_id;
                $usrGp->user_by_id = \Auth::user()->id;

                $usrGp->save();
            }

            \DB::commit();
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => $th->getMessage(), $request->all()]);
        }

        return redirect()->route('prepayroll_groups')->with('mensaje', 'Grupo de prenómina actualizado.');
    }

    public function destroy(Request $request, $id)
    {
        $oPpGroup = prepayrollGroup::find($id);

        $oPpGroup->updated_by = \Auth::user()->id;
        $oPpGroup->is_delete = 1;
        $oPpGroup->save();

        return redirect()->route('prepayroll_groups')->with('mensaje', 'Grupo de prenómina borrado.');
    }

    public function employeesVsGroups(Request $request)
    {
        $lEmployees = \DB::table('employees AS e')
                            ->leftJoin('prepayroll_group_employees AS pge', 'e.id', '=', 'pge.employee_id')
                            ->leftJoin('prepayroll_groups AS pg', 'pge.group_id', '=', 'pg.id_group')
                            ->leftJoin('prepayroll_groups_users AS pgu', 'pge.group_id', '=', 'pgu.group_id')
                            ->leftJoin('users AS u', 'pgu.head_user_id', '=', 'u.id')
                            ->select('e.*', 'pge.*', 'pg.*', 'u.name AS gr_titular')
                            ->where('e.is_delete', 0)
                            ->where('e.is_active', 1)
                            ->get();

        $groups = \DB::table('prepayroll_groups')
                            ->select('id_group', 'group_name')
                            ->where('is_delete', 0)
                            ->get();

        return view('prepayroll.indexgr')->with('lEmployees', $lEmployees)
                                            ->with('groups', $groups);
    }

    public function departmentsVsGroups(Request $request)
    {
        $lDepartments = \DB::table('departments AS d')
                            ->leftJoin('prepayroll_group_deptos AS pgd', 'd.id', '=', 'pgd.department_id')
                            ->leftJoin('prepayroll_groups AS pg', 'pgd.group_id', '=', 'pg.id_group')
                            ->leftJoin('prepayroll_groups_users AS pgu', 'pgd.group_id', '=', 'pgu.group_id')
                            ->leftJoin('users AS u', 'pgu.head_user_id', '=', 'u.id')
                            ->select('d.id AS id_department', 'd.*', 'pgd.*', 'pg.*', 'u.name AS gr_titular')
                            ->where('d.is_delete', 0)
                            ->get();

        $groups = \DB::table('prepayroll_groups')
                            ->select('id_group', 'group_name')
                            ->where('is_delete', 0)
                            ->get();

        return view('prepayroll.indexgrdepts')->with('lDepartments', $lDepartments)
                                            ->with('groups', $groups);
    }

    public function changeGroup(Request $request) {
        try {
            \DB::beginTransaction();

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

            \DB::commit();
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            return redirect()->back()->withErrors('error', $th->getMessage());
        }

        return redirect()->route('gr_emps_index')->with('mensaje', 'Grupo de prenómina actualizado.');
    }

    public function changeGroupDept(Request $request) {
        try {
            \DB::beginTransaction();

            \DB::table('prepayroll_group_deptos')
                ->where('department_id', $request->dept_id)
                ->delete();

            if ($request->new_group > 0) {
                $obj = new prepayrollGroupDepartment();
        
                $obj->group_id = $request->new_group;
                $obj->department_id = $request->dept_id;
                $obj->user_by_id = \Auth::user()->id;
        
                $obj->save();
            }

            \DB::commit();
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            return redirect()->back()->withErrors('error', $th->getMessage());
        }

        return redirect()->route('gr_depts_index')->with('mensaje', 'Grupo de prenómina actualizado.');
    }
}
