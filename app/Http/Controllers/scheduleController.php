<?php

namespace App\Http\Controllers;

use App\Models\schedule_template;
use App\Models\schedule_day;
use DateTime;
use DB;

use Illuminate\Http\Request;

class scheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = DB::table('schedule_day')
                    ->join('schedule_template','schedule_template.id','=','schedule_day.schedule_template_id')
                    ->orderBy('schedule_template.id')
                    ->orderBy('schedule_day.day_num')
                    ->where('schedule_template.is_delete','0')
                    ->select('schedule_template.id AS idSchedule','schedule_template.name AS nameTem','schedule_day.entry AS entry','schedule_day.departure AS departure','schedule_day.is_active AS active')
                    ->get();
        return view('schedule.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('schedule.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $plantilla = new schedule_template();
        $plantilla->name = $request->name;
        $plantilla->created_by = 1;
        $plantilla->updated_by = 1;
        $plantilla->save();

        $plantilla_day = new schedule_day();
        $plantilla_day->day_name = 'Lunes';
        $plantilla_day->day_num = 1;
        $plantilla_day->entry = $request->lunesE;
        $plantilla_day->departure = $request->lunesS;
        if($request->lunesE == "" || $request->lunesS == ""){
            $plantilla_day->is_active = 0;
        }else{
            $plantilla_day->is_active = 1;
        }
        $plantilla_day->schedule_template_id = $plantilla->id;
        $plantilla_day->created_by = 1;
        $plantilla_day->updated_by = 1;
        $plantilla_day->save();

        $plantilla_day = new schedule_day();
        $plantilla_day->day_name = 'Martes';
        $plantilla_day->day_num = 2;
        $plantilla_day->entry = $request->martesE;
        $plantilla_day->departure = $request->martesS;
        if($request->martesE == "" || $request->martesS == ""){
            $plantilla_day->is_active = 0;
        }else{
            $plantilla_day->is_active = 1;
        }
        $plantilla_day->schedule_template_id = $plantilla->id;
        $plantilla_day->created_by = 1;
        $plantilla_day->updated_by = 1;
        $plantilla_day->save();

        $plantilla_day = new schedule_day();
        $plantilla_day->day_name = 'Miercoles';
        $plantilla_day->day_num = 3;
        $plantilla_day->entry = $request->miercolesE;
        $plantilla_day->departure = $request->miercolesS;
        if($request->miercolesE == "" || $request->miercolesS == ""){
            $plantilla_day->is_active = 0;
        }else{
            $plantilla_day->is_active = 1;
        }
        $plantilla_day->schedule_template_id = $plantilla->id;
        $plantilla_day->created_by = 1;
        $plantilla_day->updated_by = 1;
        $plantilla_day->save();

        $plantilla_day = new schedule_day();
        $plantilla_day->day_name = 'Jueves';
        $plantilla_day->day_num = 4;
        $plantilla_day->entry = $request->juevesE;
        $plantilla_day->departure = $request->juevesS;
        if($request->juevesE == "" || $request->juevesS == ""){
            $plantilla_day->is_active = 0;
        }else{
            $plantilla_day->is_active = 1;
        }
        $plantilla_day->schedule_template_id = $plantilla->id;
        $plantilla_day->created_by = 1;
        $plantilla_day->updated_by = 1;
        $plantilla_day->save();

        $plantilla_day = new schedule_day();
        $plantilla_day->day_name = 'Viernes';
        $plantilla_day->day_num = 5;
        $plantilla_day->entry = $request->viernesE;
        $plantilla_day->departure = $request->viernesS;
        if($request->viernesE == "" || $request->viernesS == ""){
            $plantilla_day->is_active = 0;
        }else{
            $plantilla_day->is_active = 1;
        }
        $plantilla_day->schedule_template_id = $plantilla->id;
        $plantilla_day->created_by = 1;
        $plantilla_day->updated_by = 1;
        $plantilla_day->save();

        $plantilla_day = new schedule_day();
        $plantilla_day->day_name = 'Sabado';
        $plantilla_day->day_num = 6;
        $plantilla_day->entry = $request->sabadoE;
        $plantilla_day->departure = $request->sabadoS;
        if($request->sabadoE == "" || $request->sabadoS == ""){
            $plantilla_day->is_active = 0;
        }else{
            $plantilla_day->is_active = 1;
        }
        $plantilla_day->schedule_template_id = $plantilla->id;
        $plantilla_day->created_by = 1;
        $plantilla_day->updated_by = 1;
        $plantilla_day->save();

        $plantilla_day = new schedule_day();
        $plantilla_day->day_name = 'Domingo';
        $plantilla_day->day_num = 7;
        $plantilla_day->entry = $request->domingoE;
        $plantilla_day->departure = $request->domingoS;
        if($request->domingoE == "" || $request->domingoS == ""){
            $plantilla_day->is_active = 0;
        }else{
            $plantilla_day->is_active = 1;
        }
        $plantilla_day->schedule_template_id = $plantilla->id;
        $plantilla_day->created_by = 1;
        $plantilla_day->updated_by = 1;
        $plantilla_day->save();

        return redirect('schedule')->with('mensaje','Plantilla fue creada con exito');

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
        $datas = DB::table('schedule_day')
                    ->join('schedule_template','schedule_template.id','=','schedule_day.schedule_template_id')
                    ->orderBy('schedule_template.id')
                    ->orderBy('schedule_day.day_num')
                    ->where('schedule_template.is_delete','0')
                    ->where('schedule_day.schedule_template_id',$id)
                    ->select('schedule_day.day_num AS idSchedule','schedule_template.name AS nameTem','schedule_day.entry AS entry','schedule_day.departure AS departure','schedule_day.is_active AS active','schedule_template.name AS Name','schedule_day.schedule_template_id AS idTemplate')
                    ->get();
        return view('schedule.edit', compact('datas'));
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
        $schedule = schedule_template::find($id);
        $schedule->name = $request->name;
        $schedule->updated_by = 1;
        $schedule->save();
        $schedule_day = schedule_day::where('schedule_template_id',$id)
                                    ->orderBy('schedule_template_id')
                                    ->get();
        $schedule = schedule_day::find($schedule_day[0]->id);
        $schedule->entry = $request->lunesE;
        $schedule->departure = $request->lunesS;
        if($request->lunesE == '' || $request->lunesS){
            $schedule->is_active = 0;
        }else{
            $schedule->is_active = 1;
        }
        $schedule->save();

        $schedule = schedule_day::find($schedule_day[1]->id);
        $schedule->entry = $request->martesE;
        $schedule->departure = $request->martesS;
        if($request->martesE == '' || $request->martesS){
            $schedule->is_active = 0;
        }else{
            $schedule->is_active = 1;
        }
        $schedule->save();

        $schedule = schedule_day::find($schedule_day[2]->id);
        $schedule->entry = $request->miercolesE;
        $schedule->departure = $request->miercolesS;
        if($request->miercolesE == '' || $request->miercolesS){
            $schedule->is_active = 0;
        }else{
            $schedule->is_active = 1;
        }
        $schedule->save();

        $schedule = schedule_day::find($schedule_day[3]->id);
        $schedule->entry = $request->juevesE;
        $schedule->departure = $request->juevesS;
        if($request->juevesE == '' || $request->juevesS){
            $schedule->is_active = 0;
        }else{
            $schedule->is_active = 1;
        }
        $schedule->save();

        $schedule = schedule_day::find($schedule_day[4]->id);
        $schedule->entry = $request->viernesE;
        $schedule->departure = $request->viernesS;
        if($request->viernesE == '' || $request->viernesS){
            $schedule->is_active = 0;
        }else{
            $schedule->is_active = 1;
        }
        $schedule->save();

        $schedule = schedule_day::find($schedule_day[5]->id);
        $schedule->entry = $request->sabadoE;
        $schedule->departure = $request->sabadoS;
        if($request->sabadoE == '' || $request->sabadoS){
            $schedule->is_active = 0;
        }else{
            $schedule->is_active = 1;
        }
        $schedule->save();

        $schedule = schedule_day::find($schedule_day[6]->id);
        $schedule->entry = $request->domingoE;
        $schedule->departure = $request->domingoS;
        if($request->domingoE == '' || $request->domingoS){
            $schedule->is_active = 0;
        }else{
            $schedule->is_active = 1;
        }
        $schedule->save();

        
        return redirect('schedule')->with('mensaje', 'Plantilla actualizada con exito');   
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
            $schedule = schedule_template::find($id);
            $schedule->fill($request->all());
            $schedule->is_delete = 1;
            $schedule->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
