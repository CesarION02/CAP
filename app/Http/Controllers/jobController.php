<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\department;
use App\Models\area;
use App\Models\job;

class jobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = job::where('is_delete','0')->orderBy('name')->get();
        $datas->each(function($datas){
            $datas->department;
        });
        return view('job.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $department = department::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        return view('job.create')->with('departments',$department);;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $job = job::create($request->all());
        $job->updated_by = session()->get('user_id');
        $job->created_by = session()->get('user_id');
        $job->save();
        return redirect('job')->with('mensaje','Puesto fue creado con éxito');
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
        $department = department::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $data = job::findOrFail($id);
        return view('job.edit', compact('data'))->with('departments',$department);
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
        $job = job::findOrFail($id);
        $job->updated_by = session()->get('user_id');
        $job->update($request->all());
        return redirect('job')->with('mensaje', 'Puesto actualizado con éxito');
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
            $job = job::find($id);
            $job->fill($request->all());
            $job->is_delete = 1;
            $job->updated_by = session()->get('user_id');
            $job->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
