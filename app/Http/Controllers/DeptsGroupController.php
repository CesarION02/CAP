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
        $iFilter = $request->ifilter == 0 ? 1 : $request->ifilter;

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('deptsgroup.create');
    }

    public function store(Request $request)
    {
        // $obj = json_decode($request->group);

        $newGroup = new departmentsGroup();
        $newGroup->name = $request->name;
        $newGroup->is_delete = false;
        // $newGroup->updated_by = session()->get('user_id');
        // $newGroup->created_by = session()->get('user_id');

        $newGroup->save();

        // return json_encode($newGroup);
        return redirect('deptsgroup')->with('mensaje', 'El grupo fue creado con éxito');
    }

    public function edit($id, $name)
    {
        departmentsGroup::where('id', $id)
                    ->update(['name' => $name,
                                'updated_by' => session()->get('user_id')]);

        return $id;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editAll($id)
    {
        $data = departmentsGroup::findOrFail($id);
        return view('deptsgroup.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $deptsGroup = departmentsGroup::find($id);

        $deptsGroup->name = $request->name;
        // $deptsGroup->updated_by = session()->get('user_id');

        $deptsGroup->save();

        return redirect('deptsgroup')->with('mensaje', 'Grupo actualizado con éxito');
    }

    public function delete($id)
    {
        $dg = departmentsGroup::find($id);

        if ($dg->is_delete) {
            departmentsGroup::where('id', $id)
                            ->update(['is_delete' => false,
                            'updated_by' => session()->get('user_id')]);
        }
        else {
            departmentsGroup::where('id', $id)
                            ->update(['is_delete' => true,
                            'updated_by' => session()->get('user_id')]);
        }

        return $id;
    }

}
