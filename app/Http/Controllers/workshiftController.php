<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\workshift;
use App\Models\cut;


class workshiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $iFilter = $request->filter_acts == null ? 1 : $request->filter_acts;

        switch ($iFilter) {
            case 1:
                $datas = workshift::where('is_delete','0')->orderBy('name')->get();
                break;
            case 2:
                $datas = workshift::where('is_delete','1')->orderBy('name')->get();
                break;
            
            default:
                $datas = workshift::orderBy('name')->get();
                break;
        }
        
        return view('workshift.index', compact('datas'))->with('iFilter',$iFilter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $datas = cut::where('is_delete','0')->orderBy('id')->pluck('id','name');

        return view('workshift.create', compact('datas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $workshift = workshift::create($request->all());
        $workshift->updated_by = session()->get('user_id');
        $workshift->created_by = session()->get('user_id');
        $workshift->save();
        return redirect('workshift')->with('mensaje', 'Incidente creado con éxito');
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
        $datas = cut::where('is_delete','0')->orderBy('id')->pluck('id','name');
        $data = workshift::findOrFail($id);
        return view('workshift.edit', compact('data'))->with('datas',$datas);
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
        $workshift = workshift::findOrFail($id);
        $workshift->updated_by = session()->get('user_id');
        $workshift->update($request->all());
        return redirect('workshift')->with('mensaje', 'Turno actualizado con éxito');
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
            $workshift->updated_by = session()->get('user_id');
            $workshift->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }        
    }
    public function activar(Request $request,$id){
        if ($request->ajax()) {
            $workshift = workshift::find($id);
            $workshift->fill($request->all());
            $workshift->is_delete = 0;
            $workshift->updated_by = session()->get('user_id');
            $workshift->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }
}
