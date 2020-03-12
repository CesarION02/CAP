<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\employees;
use App\Models\job;
use App\Models\way_register;
use App\Models\benefitsPolice;

class employeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = employees::where('is_delete','0')->orderBy('id')->get();
        $datas->each(function($datas){
            $datas->job;
            $datas->way_register;
        });
        return view('employee.index', compact('datas'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $job = job::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        
        return view('employee.create')->with('way',$way)
                                        ->with('job',$job)
                                        ->with('benPols',$benPols);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        employees::create($request->all());
        return redirect('employee')->with('mensaje','Empleado fue creado con exito');
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
        $way = way_register::orderBy('id','ASC')->pluck('id','name');
        $job = job::where('is_delete','0')->orderBy('id','ASC')->pluck('id','name');
        $benPols = benefitsPolice::orderBy('name','ASC')->pluck('id','name');
        $data = employees::findOrFail($id);

        return view('employee.edit', compact('data'))
                                        ->with('way',$way)
                                        ->with('job',$job)
                                        ->with('benPols',$benPols);
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
        employees::findOrFail($id)->update($request->all());
        return redirect('employee')->with('mensaje', 'Empleado actualizado con exito');
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
            $employee = employees::find($id);
            $employee->fill($request->all());
            $employee->is_delete = 1;
            $employee->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
