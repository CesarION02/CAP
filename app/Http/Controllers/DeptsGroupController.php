<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departmentsGroup;
use App\Models\department;
use DB;

class DeptsGroupController extends Controller
{
    public function index(Request $request) 
    {
        $iFilter = $request->filter_acts == null ? 1 : $request->filter_acts;

        $lGroups = DB::table('department_group AS dg')->selectRaw('id, name, is_delete, "" AS depts');

        switch ($iFilter) {
            case 1:
                $lGroups = $lGroups->where('is_delete', false);
                break;
            case 2:
                $lGroups = $lGroups->where('is_delete', true);
                break;
            
            default:
                # code...
                break;
        }
        $lGroups = $lGroups->get();
        
        $lDepts = department::where('is_delete', false)
                                ->select('id', 'name', 'dept_group_id')
                                ->get();

        return view('deptsgroup.index')
                    ->with('lGroups', $lGroups)
                    ->with('lDepts', $lDepts)
                    ->with('iFilter', $iFilter);
    }

    public function store(Request $request)
    {
        $obj = json_decode($request->group);

        $newGroup = new departmentsGroup();
        $newGroup->name = $obj->name;
        $newGroup->is_delete = false;

        $newGroup->save();

        return json_encode($newGroup);
    }

    public function edit($id, $name)
    {
        departmentsGroup::where('id', $id)
                    ->update(['name' => $name]);

        return $id;
    }

    public function delete($id)
    {
        $dg = departmentsGroup::find($id);

        if ($dg->is_delete) {
            departmentsGroup::where('id', $id)
                            ->update(['is_delete' => false]);
        }
        else {
            departmentsGroup::where('id', $id)
                            ->update(['is_delete' => true]);
        }

        return $id;
    }

}
