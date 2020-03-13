<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\holiday;
use App\Models\holidayAux;

class holidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = holiday::where('is_delete','0')->orderBy('id')->get();
        return view('holiday.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('holiday.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        holiday::create($request->all());
        return redirect('holidays')->with('mensaje', 'Día Festivo creado con exito');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAux(Request $request)
    {
        $req = json_decode($request->hol_aux);

        $hAux = new holidayAux();

        $hAux->text_description = $req->text_description;
        $hAux->dt_date = $req->dt_date;
        $hAux->holiday_id = (! $req->holiday_id > 0) ? null : $req->holiday_id;
        $hAux->is_delete = false;
        $hAux->created_by = 1;
        $hAux->updated_by = 1;

        $hAux->save();
        
        return json_encode($hAux);
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
        $data = holiday::findOrFail($id);
        return view('holiday.edit', compact('data'));
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
        holiday::findOrFail($id)->update($request->all());
        return redirect('holidays')->with('mensaje', 'Dia Festivo actualizado con exito');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAux(Request $request, $id)
    {
        holidayAux::findOrFail($id)->update($request->all());
        
        return $id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($request->ajax()) {
            $holiday = holiday::find($id);
            $holiday->fill($request->all());
            $holiday->is_delete = 1;
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        } 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyAux($id)
    {
        $oHolidayAux = holidayAux::find($id);

        if ($oHolidayAux->is_delete) {
            holidayAux::where('id', $id)
                            ->update(['is_delete' => false]);
        }
        else {
            holidayAux::where('id', $id)
                            ->update(['is_delete' => true]);
        }

        return $id;
    }
}
