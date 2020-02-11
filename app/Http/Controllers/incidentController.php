<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\incident;
use App\Models\typeincident;
use App\Models\employees;
use App\Http\Requests\ValidacionTypeincident;

class incidentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = incident::where('is_delete','0')->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->typeincident;
            $datas->employee;
        });
        return view('incident.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $incidents = typeincident::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $employees = employees::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        return view('incident.create')->with('incidents',$incidents)->with('employees',$employees);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        incident::create($request->all());
        return redirect('incidents')->with('mensaje', 'Incidente creado con exito');
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
        $incidents = typeincident::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $employees = employees::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $data = incident::findOrFail($id);
        return view('incident.edit', compact('data'))->with('incidents',$incidents)->with('employees',$employees);
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
        incident::findOrFail($id)->update($request->all());
        return redirect('incidents')->with('mensaje', 'Incidente actualizado con exito');
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
            $incident = incident::find($id);
            $incident->fill($request->all());
            $incident->is_delete = 1;
            $incident->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
