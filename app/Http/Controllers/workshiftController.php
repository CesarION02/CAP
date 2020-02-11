<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\workshift;


class workshiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = workshift::where('is_delete','0')->orderBy('id')->get();
        return view('workshift.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('workshift.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        workshift::create($request->all());
        return redirect('workshift')->with('mensaje', 'Incidente creado con exito');
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
        $data = workshift::findOrFail($id);
        return view('workshift.edit', compact('data'));
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
        workshift::findOrFail($id)->update($request->all());
        return redirect('workshift')->with('mensaje', 'Turno actualizado con exito');
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
            $workshift = workshift::find($id);
            $workshift->fill($request->all());
            $workshift->is_delete = 1;
            $workshift->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }        
    }
}
