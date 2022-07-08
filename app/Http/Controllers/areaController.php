<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\area;
use App\Models\employees;
use App\Models\policyHoliday;

class areaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $iFilter = $request->ifilter == 0 ? 1 : $request->ifilter;

        switch ($iFilter) {
            case 1:
                $datas = area::where('is_delete','0')->orderBy('name')->get();
                break;
            case 2:
                $datas = area::where('is_delete','1')->orderBy('name')->get();
                break;
            
            default:
                $datas = area::orderBy('name')->get();
                break;
        }

        $datas->each(function($datas){
            $datas->boss;
            $datas->policyHoliday;
        });
        
        return view('area.index', compact('datas'))->with('iFilter',$iFilter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $employees = employees::where('is_active','1')->orderBy('name','ASC')->pluck('id','name');
        $policyh = policyHoliday::orderBy('id','ASC')->pluck('id','name');
        return view('area.create')->with('employees',$employees)->with('policyh',$policyh);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }
        $area = area::create($request->all());
        $id = session()->get('user_id');
        $name = session()->get('name');
        $area->boss_id = $request->boss_id;
        $area->policy_holiday_id = $request->policy_holiday_id;
        $area->updated_by = session()->get('user_id');
        $area->created_by = session()->get('user_id');
        $area->save();
        return redirect('area')->with('mensaje','Área fue creada con éxito');
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
        $employees = employees::where('is_active','1')->orderBy('name','ASC')->pluck('id','name');
        $policyh = policyHoliday::orderBy('id','ASC')->pluck('id','name');
        return view('area.edit', compact('data'))->with('employees',$employees)->with('policyh',$policyh);
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
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }
        $area = area::findOrFail($id);
        $area->updated_by = session()->get('user_id');
        $area->boss_id = $request->boss_id;
        $area->policy_holiday_id = $request->policy_holiday_id;
        $area->update($request->all());
        return redirect('area')->with('mensaje', 'Área actualizada con éxito');
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
    public function activar(Request $request,$id){
        if ($request->ajax()) {
            $area = area::find($id);
            $area->fill($request->all());
            $area->is_delete = 0;
            $area->updated_by = session()->get('user_id');
            $area->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }
}
