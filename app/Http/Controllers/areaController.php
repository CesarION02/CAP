<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\area;

class areaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = area::where('is_delete','0')->orderBy('id')->get();
        return view('area.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('area.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $area = area::create($request->all());
        $id = session()->get('user_id');
        $name = session()->get('name');
        $area->updated_by = session()->get('user_id');
        $area->created_by = session()->get('user_id');
        $area->save();
        return redirect('area')->with('mensaje','Area fue creada con exito');
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
        $data = area::findOrFail($id);
        
        return view('area.edit', compact('data'));
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
        $area = area::findOrFail($id);
        $area->updated_by = session()->get('user_id');
        $area->update($request->all());
        return redirect('area')->with('mensaje', 'Area actualizada con exito');
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
            $incident = area::find($id);
            $incident->fill($request->all());
            $incident->is_delete = 1;
            $incident->updated_by = session()->get('user_id');
            $incident->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
