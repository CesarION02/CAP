<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\incident;
use App\Models\typeincident;
use App\Models\employees;
use App\Http\Requests\ValidacionTypeincident;

class incidentController extends Controller
{
    private $employees;
    private $absTypeKeys;

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

    /**
     * Undocumented function
     *
     * @param [type] $lAbsences
     * @return void
     */
    public function saveAbsencesFromJSON($lAbsences)
    {
        $this->absTypeKeys = [
                                '1_1' => '1',
                                '1_2' => '2',
                                '1_3' => '3',
                                '1_4' => '4',
                                '1_5' => '5',
                                '1_6' => '6',
                                '1_7' => '7',
                                '2_1' => '8',
                                '2_2' => '9',
                                '2_3' => '10',
                                '2_4' => '11',
                                '3_1' => '12',
                                '3_2' => '13',
                            ];

        $lCapAbss = incident::select('id', 'external_key')
                                    ->pluck('id', 'external_key');

        $this->employees = employees::select('id', 'external_id')
                            ->pluck('id', 'external_id');
                            
        foreach ($lAbsences as $jAbs) {
            try {
                $id = $lCapAbss[$jAbs->id_emp.'_'.$jAbs->id_abs];
                $this->updIncident($jAbs, $id);
            }
            catch (\Throwable $th) {
                $this->insertIncident($jAbs);
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $jAbs
     * @param [type] $id
     * @return void
     */
    private function updIncident($jAbs, $id)
    {
        incident::where('id', $id)
                    ->update(
                            [
                                'num' => $jAbs->num,
                                'type_incidents_id' => $this->absTypeKeys[$jAbs->fk_class_abs.'_'.$jAbs->fk_type_abs],
                                'cls_inc_id' => $jAbs->fk_class_abs,
                                'start_date' => $jAbs->dt_start,
                                'end_date' => $jAbs->dt_end,
                                'eff_day' => $jAbs->eff_days,
                                'ben_year' => $jAbs->ben_year,
                                'ben_ann' => $jAbs->ben_ann,
                                'nts' => $jAbs->notes,
                                'employee_id' => $this->employees[$jAbs->id_emp],
                                'is_delete' => $jAbs->is_deleted,
                            ]
                        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $jAbs
     * @return void
     */
    private function insertIncident($jAbs)
    {
        $abs = new incident();

        $abs->num = $jAbs->num;
        $abs->type_incidents_id = $this->absTypeKeys[$jAbs->fk_class_abs.'_'.$jAbs->fk_type_abs];
        $abs->cls_inc_id = $jAbs->fk_class_abs;
        $abs->start_date = $jAbs->dt_start;
        $abs->end_date = $jAbs->dt_end;
        $abs->eff_day = $jAbs->eff_days;
        $abs->ben_year = $jAbs->ben_year;
        $abs->ben_ann = $jAbs->ben_ann;
        $abs->nts = $jAbs->notes;
        $abs->external_key = $jAbs->id_emp.'_'.$jAbs->id_abs;
        $abs->employee_id = $this->employees[$jAbs->id_emp];
        $abs->is_delete = $jAbs->is_deleted;
        $abs->created_by = 1;
        $abs->updated_by = 1;

        $abs->save();
    }
}
