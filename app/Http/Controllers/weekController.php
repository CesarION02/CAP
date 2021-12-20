<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Models\week_cut;

class weekController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $week = DB::table('week_cut')
                    ->groupby('year')
                    ->select('year AS year')
                    ->get();
        return view('week.index')->with('year',$week);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('week.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $dia = DB::table('first_day_year')
                    ->where('year',$request->year)
                    ->get();
        $num = 1;
        $primerDia = Carbon::parse($dia[0]->dt_date);
        $ultimoDia = Carbon::parse($dia[0]->dt_date);
        $ultimoDia->addDays(6);
        for($i = 0 ; 52 > $i ; $i++){
            $semana = new week_cut();
            $semana->ini = $primerDia->toDateString();
            $semana->year = $request->year;
            $semana->fin = $ultimoDia->toDateString();
            $semana->num = $num;
            $semana->updated_by = session()->get('user_id');
            $semana->created_by = session()->get('user_id');
            $semana->save();
            $num++;
            $primerDia->addDays(7);
            $ultimoDia->addDays(7);

            $prepayroll = new prepayroll_control();
            $prepayroll->status = 1;
            $prepayroll->num_week = $semana->id;
            $prepayroll->is_week = 1;
            $prepayroll->created_by = session()->get('user_id');
            $prepayroll->updated_by = session()->get('user_id');
            $prepayroll->save();
        }

        return redirect('week')->with('mensaje', 'Semanas creadas con exito');

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
        //
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
        //
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
            
            week_cut::where('year',$id)->delete();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }

    public function primerdia(Request $request){
        $dia = DB::table('first_day_year')
                    ->where('year',$request->year)
                    ->get();
        return response()->json($dia);
    }
}
