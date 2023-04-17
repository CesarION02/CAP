<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\incident;
use Illuminate\Http\Request;
use App\Models\typeincident;
use App\Http\Requests\ValidacionTypeincident;


class typeincidentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = typeincident::where('is_delete','0')->orderBy('id')->get();
        return view('typeincident.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('typeincident.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ValidacionTypeincident $request)
    {
        typeincident::create($request->all());
        return redirect()->route('tipos_index')->with('mensaje', 'Tipo Incidente creado con exito');
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
        $data = typeincident::findOrFail($id);
        return view('typeincident.edit', compact('data'));
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
        typeincident::findOrFail($id)->update($request->all());
        return redirect('tipos_index')->with('mensaje', 'Tipo de Incidente actualizado con exito');
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
            $incident = typeincident::find($id);
            $incident->fill($request->all());
            $incident->is_delete = 1;
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }

    public function updateIncident(Request $request)
    {
        $oIncident = typeincident::find($request->id_inc_type);
        $oIncident->{$request->attribute_nm} = $request->new_value;
        try {
            $oIncident->save();
        }
        catch (\Throwable $th) {
            \Log::error($th);
            return json_encode($th);
        }

        $lIncidentTypes = \DB::table('type_incidents')->get();

        return json_encode($lIncidentTypes);
    }
}
