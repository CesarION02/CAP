<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidacionTypeincident;
use App\Models\adjust_link;
use App\Models\company;
use App\Models\employees;
use App\Models\holidayworked;
use App\Models\incident;
use App\Models\incidentDay;
use App\Models\IncidentExtSysLink;
use App\Models\prepayrollAdjust;
use App\Models\typeincident;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SPrepayrollAdjustUtils;
use App\SValidations\SIncidentValidations;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class incidentController extends Controller
{
    private $employees;
    private $absTypeKeys;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($incidentType = 0, Request $request)
    {
        $start_date = null;
        $end_date = null;
        $is_medical = 0;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $start_date = $now->startOfMonth()->toDateString();
            $end_date = $now->endOfMonth()->toDateString();
        }
        else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }

        if (session()->get('rol_id') != 1){

            if (session()->get('rol_id') == 16){
                $datas = incident::orderBy('incidents.id')
                            ->join('type_incidents','incidents.type_incidents_id',"=",'type_incidents.id');
                if ($incidentType > 0) {
                    $datas = $datas->where('is_agreement', 1);
                }

                $datas = $datas->whereBetween('start_date', [$start_date, $end_date])
                            ->join('employees','employees.id','=','incidents.employee_id')
                            ->join('departments','departments.id','=','employees.department_id')
                            ->where('is_active', true)
                            ->where('incidents.is_delete','0')
                            ->where('type_incidents.id','22')
                            ->select('incidents.id AS id','incidents.start_date AS ini','incidents.end_date AS fin','employees.name AS name','type_incidents.name AS tipo');
                $datas = $datas->get();  
                
                $is_medical = 1;

            }else{
                $numero = session()->get('name');
                $usuario = DB::table('users')
                        ->where('name',$numero)
                        ->get();
                $dgu = DB::table('group_dept_user')
                        ->where('user_id',$usuario[0]->id)
                        ->select('groupdept_id AS id')
                        ->get();
                $Adgu = [];
                for($i=0;count($dgu)>$i;$i++){
                    $Adgu[$i]=$dgu[$i]->id;
                }
                
                $datas = incident::orderBy('incidents.id')
                                ->join('type_incidents','incidents.type_incidents_id',"=",'type_incidents.id');
                if ($incidentType > 0) {
                    $datas = $datas->where('is_agreement', 1);
                }

                $datas = $datas->whereBetween('start_date', [$start_date, $end_date])
                            ->join('employees','employees.id','=','incidents.employee_id')
                            ->join('departments','departments.id','=','employees.department_id')
                            ->whereIn('departments.dept_group_id',$Adgu)
                            ->where('is_active', true)
                            ->where('incidents.is_delete','0')
                            ->select('incidents.id AS id','incidents.start_date AS ini','incidents.end_date AS fin','employees.name AS name','type_incidents.name AS tipo');
                $datas = $datas->get();
            }
        }else{
            $datas = incident::orderBy('incidents.id')
                            ->join('type_incidents','incidents.type_incidents_id',"=",'type_incidents.id');
            if ($incidentType > 0) {
                $datas = $datas->where('is_agreement', 1);
            }

            $datas = $datas->whereBetween('start_date', [$start_date, $end_date])
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('departments','departments.id','=','employees.department_id')
                        ->where('is_active', true)
                        ->where('incidents.is_delete','0')
                        ->select('incidents.id AS id','incidents.start_date AS ini','incidents.end_date AS fin','employees.name AS name','type_incidents.name AS tipo');
            $datas = $datas->get();        
        }

        $sroute = 'incidentes';

        return view('incident.index')
                    ->with('incidentType', $incidentType)
                    ->with('datas', $datas)
                    ->with('sroute', $sroute)
                    ->with('is_medical', $is_medical)
                    ->with('start_date', $start_date)
                    ->with('end_date', $end_date);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create($incidentType = 0)
    {
        $employees = DB::table('employees')
                        ->join('departments', 'departments.id', '=', 'employees.department_id')
                        ->where('is_active', true)
                        ->select('employees.name AS name', 'employees.id AS num', 'employees.num_employee')
                        ->orderBy('name', 'ASC');

        if (session()->get('rol_id') != 1) {
            if (session()->get('rol_id') != 16) {
                $numero = session()->get('name');
                $usuario = DB::table('users')
                    ->where('name', $numero)
                    ->get();
                $dgu = DB::table('group_dept_user')
                    ->where('user_id', $usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
                $Adgu = [];
                for ($i = 0; count($dgu) > $i; $i++) {
                    $Adgu[$i] = $dgu[$i]->id;
                }

                $employees = $employees->whereIn('departments.dept_group_id', $Adgu)->get();
            }
            else {
                $employees = $employees->get();
            }
        }
        else {
            $employees = $employees->get();
        }

        // Obtiene la configuración de las incidencias que deben ingresar un comentario obligatorio
        $lCommControl = \DB::table('comments_control')->where('value', 1)
                                                        ->select('key_code AS id')
                                                        ->whereRaw('key_code REGEXP "^[0-9]+$"')
                                                        ->pluck('id');
        
        // Obtiene los días festivos
        $holidays = \DB::table('holidays')->where('is_delete', 0)
                                        ->orderBy('fecha', 'DESC')
                                        ->get();

        // Obtiene los comentarios frecuentes
        $lFrecuentComments = \DB::table('comments')->where('is_delete', 0)
                                                    ->orderBy('comment', 'ASC')
                                                    ->get();

        $lIncidentTypes = typeincident::orderBy('name', 'ASC')
                                        ->where('is_cap_edit', true);

        if (session()->get('rol_id') == 16) {
            $lIncidentTypes = $lIncidentTypes->where('id', 22);
        }

        $lIncidentTypes = $lIncidentTypes->select('id', 'name', 'has_subtypes')
                                        ->get();

        $lSubTypes = \DB::table('type_sub_incidents')
                        ->where('is_delete', 0)
                        ->select('id_sub_incident', 'name', 'is_default', 'incident_type_id')
                        ->orderBy('name', 'ASC')
                        ->get();

        return view('incident.create')
                        ->with('incidentType', $incidentType)
                        ->with('lIncidentTypes', $lIncidentTypes)
                        ->with('lSubTypes', $lSubTypes)
                        ->with('employees', $employees)
                        ->with('lCommControl', $lCommControl)
                        ->with('holidays', $holidays)
                        ->with('lFrecuentComments', $lFrecuentComments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ],
            [
                'start_date.required' => 'Debe seleccionar una fecha inicial',
                'end_date.required' => 'Debe seleccionar una fecha final',
            ]
        );

        if ($request->employee_id == 0 || is_null($request->employee_id) || !isset($request->employee_id)) {
            return redirect()->back()->withErrors('Debe seleccionar un empleado');
        }
        if ($request->type_incidents_id == 0 || is_null($request->type_incidents_id) || !isset($request->type_incidents_id)) {
            return redirect()->back()->withErrors('Debe seleccionar un tipo de incidencia');
        }

        try {
            \DB::beginTransaction();

            $resp = SIncidentValidations::validateIncidentsAndHolidays($request->start_date, $request->end_date, $request->employee_id, 0);
            if ($resp['status'] == 'error') {
                return redirect('/incidents/14')->with('mensaje', $resp['message']);
            }

            $idHolidayWorked = 0;
            if ($request->type_incidents_id == 17) {
                $holiday_worked = new holidayworked();
                $holiday_worked->employee_id = $request->employee_id;
                $holiday_worked->holiday_id = $request->holiday_id;
                $holiday_worked->number_assignments = 0;
                $holiday_worked->is_delete = 0;
                $holiday_worked->save();

                $idHolidayWorked = $holiday_worked->id;
            }

            $incident = new incident($request->all());
            $incident = SIncidentValidations::manageIncident($incident, $request->comentarios, $idHolidayWorked);
            $incident->is_external = false;
            $incident->is_delete = 0;
            $incident->created_by = session()->get('user_id');
            $incident->updated_by = session()->get('user_id');

            $incident->save();

            //inserción de comentarios
            if (isset($request->comentarios) && !is_null($request->comentarios) && strlen($request->comentarios) > 0) {
                $dateI = Carbon::parse($request->start_date);
                $dateS = Carbon::parse($request->end_date);

                $diferencia = ($dateI->diffInDays($dateS));
                for ($i = 0; $diferencia >= $i; $i++) {
                    $adjust = new prepayrollAdjust();
                    $adjust->employee_id = $request->employee_id;
                    $adjust->dt_date = $dateI->toDateString();
                    $adjust->minutes = 0;
                    $adjust->apply_to = 2;
                    $adjust->comments = $request->comentarios;
                    $adjust->is_delete = 0;
                    $adjust->is_external = 0;
                    $adjust->adjust_type_id = \SCons::PP_TYPES['COM'];
                    $adjust->apply_time = 0;
                    $adjust->created_by = session()->get('user_id');
                    $adjust->updated_by = session()->get('user_id');
                    $adjust->save();

                    $link = new adjust_link();
                    $link->adjust_id = $adjust->id;
                    $link->is_incident = 1;
                    $link->incident_id = $incident->id;
                    $link->save();

                    $dateI->addDay();
                }
            }

            $incidentController = new incidentController();
            $incidentController->saveDays($incident);

            \DB::commit();
        
            if ($request->incident_type > 0) {
                return redirect()->route('incidentes', [$request->incident_type])->with('mensaje', 'Incidente creado con éxito');
            } else {
                return redirect('incidents')->with('mensaje', 'Incidente creado con éxito');
            }   
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);

            return redirect()->back()->withErrors($th->getMessage())->withInput($request->input());
        }
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
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit($idIncidence, $is_medical = 0)
    {
        $lIncidentTypes = typeincident::orderBy('name', 'ASC')
                            ->where('is_cap_edit', true);

        if (session()->get('rol_id') == 16) {
            $lIncidentTypes = $lIncidentTypes->where('id', 22);
        }

        $lIncidentTypes = $lIncidentTypes->select('id', 'name', 'has_subtypes')
                                        ->get();

        $lSubTypes = \DB::table('type_sub_incidents')
                        ->where('is_delete', 0)
                        ->select('id_sub_incident', 'name', 'is_default', 'incident_type_id')
                        ->orderBy('name', 'ASC')
                        ->get();

        $datas = DB::table('incidents')
            ->join('employees', 'employees.id', "=", "incidents.employee_id")
            ->where('incidents.id', $idIncidence)
            ->select('incidents.id AS id', 
                    'incidents.employee_id AS id_employee', 
                    'employees.name AS name', 
                    'employees.num_employee', 
                    'incidents.start_date AS ini', 
                    'incidents.end_date AS fin', 
                    'incidents.holiday_worked_id', 
                    'type_incidents_id AS tipo',
                    'type_sub_inc_id AS subtipo')
            ->first();

        $oAdjust = adjust_link::where('incident_id', $datas->id)
                                    ->join('prepayroll_adjusts AS adjs', 'adjust_link.adjust_id', '=', 'adjs.id')
                                    ->where('adjs.is_delete', 0)
                                    ->where('adjust_type_id', \SCons::PP_TYPES['COM'])
                                    ->select('adjust_link.*', 'adjs.comments')
                                    ->orderBy('updated_by', 'DESC')
                                    ->first();

        // Obtiene la configuración de las incidencias que deben ingresar un comentario obligatorio
        $lCommControl = \DB::table('comments_control')->where('value', 1)
            ->select('key_code AS id')
            // filtro para los id de las incidencias que requieren comentario (son numéricas)
            ->whereRaw('key_code REGEXP "^[0-9]+$"')
            ->pluck('id');

        // Obtiene los días festivos
        $holidays = \DB::table('holidays')->where('is_delete', 0)
            ->orderBy('fecha', 'DESC')
            ->get();

        // Obtiene los comentarios frecuentes
        $lFrecuentComments = \DB::table('comments')->where('is_delete', 0)
            ->orderBy('comment', 'ASC')
            ->get();

        $incidentTypeId = $datas->tipo;
        $iIdHoliday = 0;
        if (! is_null($datas->holiday_worked_id)) {
            $oHW = holidayworked::find($datas->holiday_worked_id);
            $iIdHoliday = $oHW->holiday_id;
        }

        return view('incident.edit')
                        ->with('datas', $datas)
                        ->with('oAdjust', $oAdjust)
                        ->with('is_medical', $is_medical)
                        ->with('idIncidence', $idIncidence)
                        ->with('incidentTypeId', $incidentTypeId)
                        ->with('lIncidentTypes', $lIncidentTypes)
                        ->with('lSubTypes', $lSubTypes)
                        ->with('lCommControl', $lCommControl)
                        ->with('holidays', $holidays)
                        ->with('iIdHoliday', $iIdHoliday)
                        ->with('lFrecuentComments', $lFrecuentComments);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            incident::findOrFail($id)->update($request->all());
            $oIncident = incident::find($id);

            $adjust_delete = DB::table('adjust_link')
                                ->where('incident_id', $id)
                                ->pluck('adjust_id');

            DB::beginTransaction();
            
            prepayrollAdjust::whereIn('id', $adjust_delete)
                        ->where('is_delete', 0)
                        ->update(['is_delete' => 1]);

            if (! isset($oIncident->type_sub_inc_id) || is_null($oIncident->type_sub_inc_id)) {
                $oType = typeincident::find($oIncident->type_incidents_id);
                if ($oType->has_subtypes) {
                    $lSubTypes = DB::table('type_sub_incidents')->where('incident_type_id', $oType->id)
                                    ->where('is_delete', 0)
                                    ->orderBy('updated_at', 'DESC')
                                    ->get();

                    if (count($lSubTypes) > 0) {
                        $default = 0;
                        foreach ($lSubTypes as $oSubType) {
                            if ($oSubType->is_default) {
                                $default = $oSubType->id_sub_incident;
                                break;
                            }
                        }
                        if ($default == 0) {
                            $default = $lSubTypes[0]->id_sub_incident;
                        }
                        $oIncident->type_sub_inc_id = $default;
                    }
                    else {
                        $oIncident->type_sub_inc_id = null;
                    }
                }

                $oIncident->save();
            }

            if ($oIncident->type_incidents_id == 17) {
                if (! is_null($oIncident->holiday_worked_id)) {
                    $oWH = holidayworked::find($oIncident->holiday_worked_id);

                    if ($request->holiday_id != $oWH->holiday_id) {
                        $oWH->holiday_id = $request->holiday_id;
                        $oWH->save();
                    }
                }
                else {
                    $holiday_worked = new holidayworked();
                    $holiday_worked->employee_id = $request->employee_id;
                    $holiday_worked->holiday_id = $request->holiday_id;
                    $holiday_worked->number_assignments = 0;
                    $holiday_worked->is_delete = 0;
                    $holiday_worked->save();
                }
            }

            if (isset($request->comentarios) && ! is_null($request->comentarios) && strlen($request->comentarios) > 0) {
                $dateI = Carbon::parse($request->start_date);
                $dateS = Carbon::parse($request->end_date);
        
                $diferencia = ($dateI->diffInDays($dateS));
                for ($i = 0; $diferencia >= $i; $i++) {
                    $adjust = new prepayrollAdjust();
                    $adjust->employee_id = $request->employee_id;
                    $adjust->dt_date = $dateI->toDateString();
                    $adjust->minutes = 0;
                    $adjust->apply_to = 2;
                    $adjust->comments = $request->comentarios;
                    $adjust->is_delete = 0;
                    $adjust->is_external = 0;
                    $adjust->adjust_type_id = \SCons::PP_TYPES['COM'];
                    $adjust->apply_time = 0;
                    $adjust->created_by = session()->get('user_id');
                    $adjust->updated_by = session()->get('user_id');
                    $adjust->save();
        
                    $link = new adjust_link();
                    $link->adjust_id = $adjust->id;
                    $link->is_incident = 1;
                    $link->incident_id = $id;
                    $link->save();
        
                    $dateI->addDay();
                }
            }

            $oIncident->eff_day = Carbon::parse($oIncident->start_date)->diffInDays($oIncident->end_date) + 1;
            $oIncident->save();

            $incidentController = new incidentController();
            $incidentController->saveDays($oIncident);

            \DB::commit();

            if ($request->is_medical == 1) {
                return redirect()->route('incidentes', 14)->with('mensaje', 'Incidencia modificada con éxito');
            }
            else {
                return redirect('incidents')->with('mensaje', 'Incidencia modificada con éxito');
            }
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);

            return redirect()->back()->withErrors($th->getMessage())->withInput($request->input());
        }
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

            //sacar ajustes que tendran que borrarse

            $adjust_delete = DB::table('adjust_link')
                    ->where('incident_id',$incident->id)
                    ->get();
            
            //$adjust_delete->toArray();

            for( $i = 0 ; count($adjust_delete) > $i ; $i++ ){
                $delete = prepayrollAdjust::where('id',$adjust_delete[$i]->adjust_id)->get();
                $delete[0]->is_delete = 1;
                $delete[0]->save();
            }

            $adjust_delete = DB::table('adjust_link')
                    ->where('incident_id',$incident->id)
                    ->delete();
                    
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
                                '1_8' => '16',
                                '1_9' => '18',
                                '1_10' => '20',
                            ];
        
        foreach ($lAbsences as $jAbs) {
            $this->employees = employees::select('id', 'external_id')
                            ->pluck('id', 'external_id');
            $lCapAbss = incident::select('incidents.id AS idincident', 'iesl.external_key','companies.id AS idcompany')
                                ->join('companies','companies.id','=','incidents.company_id')
                                ->join('incident_ext_sys_links AS iesl','iesl.incident_id','=','incidents.id')
                                ->where('iesl.external_key', "".$jAbs->id_emp."_".$jAbs->id_abs."")
                                ->where('companies.db_name',$jAbs->company)
                                ->where('iesl.external_system', 'siie')
                                ->get();
            try {
                if(count($lCapAbss) >= 1){
                        $id = $lCapAbss[0]->idincident;
                        $company = $lCapAbss[0]->idcompany;
                        $oIncident = $this->updIncident($jAbs, $id, $company);
                }else{
                    $lCapAbss = company::select('companies.id AS idcompany')
                                ->where('companies.db_name',$jAbs->company)
                                ->get();
                    $company = $lCapAbss[0]->idcompany;    
                    $oIncident = $this->insertIncident($jAbs,$company);
                }

                $this->saveDays($oIncident);
            }catch (\Throwable $th) {
                \Log::error($th->getMessage());
                $error = $th;
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
    private function updIncident($jAbs, $id, $company)
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
                                'company_id' => $company,
                            ]
                        );

        return incident::find($id);
    }

    /**
     * Undocumented function
     *
     * @param \stdClass $jAbs
     * @param int $company
     * 
     * @return incident
     */
    private function insertIncident($jAbs, $company)
    {
        $abs = new incident();

        $abs->is_external = true;
        $abs->num = $jAbs->num;
        $abs->type_incidents_id = $this->absTypeKeys[$jAbs->fk_class_abs.'_'.$jAbs->fk_type_abs];
        $abs->cls_inc_id = $jAbs->fk_class_abs;
        $abs->start_date = $jAbs->dt_start;
        $abs->end_date = $jAbs->dt_end;
        $abs->eff_day = $jAbs->eff_days;
        $abs->ben_year = $jAbs->ben_year;
        $abs->ben_ann = $jAbs->ben_ann;
        $abs->nts = $jAbs->notes;
        $abs->employee_id = $this->employees[$jAbs->id_emp];
        $abs->is_delete = $jAbs->is_deleted;
        $abs->created_by = 1;
        $abs->updated_by = 1;
        $abs->company_id = $company;

        $abs->save();
        
        $link = new IncidentExtSysLink();
        $link->external_key = $jAbs->id_emp.'_'.$jAbs->id_abs;
        $link->external_system = 'siie';
        $link->incident_id = $abs->id;
        $link->save();

        return $abs;
    }

    public function daysIncidents($incident_id,$ini,$fin,$employee_id)
    {
        $oStartDate = Carbon::parse($ini.' 00:00:00');
        $oEndDate = Carbon::parse($fin.' 00:00:00');
        $oDate = clone $oStartDate;

        $days = [];
        $dayCounter = 1;
        while ($oDate->lessThanOrEqualTo($oEndDate)) {
            $sDate = $oDate->toDateString();
            $day = new incidentDay();
            $day->incidents_id = $incident_id;
            $day->date = $sDate;
            $day->num_day = $dayCounter;
            $day->is_delete = 0;

            $day->save();
            $dayCounter++;
            $oDate->addDay();
        }
    }

    public function saveDays($oIncident)
    {
        incidentDay::where('incidents_id', $oIncident->id)->delete();

        $oStartDate = Carbon::parse($oIncident->start_date.' 00:00:00');
        $oEndDate = Carbon::parse($oIncident->end_date.' 00:00:00');
        $oDate = clone $oStartDate;

        $days = [];
        $dayCounter = 1;
        while ($oDate->lessThanOrEqualTo($oEndDate) && ($dayCounter <= $oIncident->eff_day || ($oIncident->cls_inc_id == \SCons::CL_VACATIONS && ! $oIncident->is_external))) {
            $sDate = $oDate->toDateString();

            switch ($oIncident->cls_inc_id) {
                case \SCons::CL_VACATIONS:
                    $qWorkshifts = SDelayReportUtils::getWorkshifts($sDate, $sDate, 0, [$oIncident->employee_id]);
                    $registryAux = (object) [
                        'type_id' => \SCons::REG_OUT,
                        'time' => "12:00:00",
                        'date' => $sDate,
                        'employee_id' => $oIncident->employee_id
                    ];

                    $result = SDelayReportUtils::getSchedule($sDate, $sDate, $oIncident->employee_id, $registryAux, clone $qWorkshifts, \SCons::REP_HR_EX);
                    if (is_null($result) || (!is_null($result->auxScheduleDay) && !$result->auxScheduleDay->is_active)) {
                        break;
                    }

                case \SCons::CL_ABSENCE:
                case \SCons::CL_INHABILITY:
                default:
                    $day = new incidentDay();
                    $day->incidents_id = $oIncident->id;
                    $day->date = $sDate;
                    $day->num_day = $dayCounter;
                    $day->is_delete = $oIncident->is_delete;

                    $days[] = $day;
                    $dayCounter++;
                    break;
            }

            // SPrepayrollAdjustUtils::verifyProcessedData($oIncident->employee_id, $sDate);
            $oDate->addDay();
        }

        if ($oIncident->cls_inc_id == \SCons::CL_VACATIONS && ! $oIncident->is_external && $oIncident->eff_days == 0) {
            $oIncident->eff_day = $dayCounter - 1;
            $oIncident->save();
        }

        if (sizeof($days) > 0) {
            $oIncident->incidentDays()->saveMany($days);
        }
    }

    public function massiveCreate(){
        $incidents = typeincident::orderBy('name','ASC');
        $incidents = $incidents->where('is_agreement', 1);
        $incidents = $incidents->pluck('id','name');

        if (session()->get('rol_id') != 1){
            $numero = session()->get('name');
            $usuario = DB::table('users')
                    ->where('name',$numero)
                    ->get();
            $dgu = DB::table('group_dept_user')
                    ->where('user_id',$usuario[0]->id)
                    ->select('groupdept_id AS id')
                    ->get();
            $Adgu = [];
            for($i=0;count($dgu)>$i;$i++){
                $Adgu[$i]=$dgu[$i]->id;
            }
            
            $employees = DB::table('employees')
                                ->join('departments','departments.id','=','employees.department_id')
                                ->whereIn('departments.dept_group_id',$Adgu)
                                ->where('is_active', true)
                                ->orderBy('name','ASC')
                                ->select('employees.name AS name','employees.num_employee AS num')
                                ->get();
        }else{
            $employees = DB::table('employees')
                                ->join('departments','departments.id','=','employees.department_id')
                                ->where('is_active', true)
                                ->where('department_id',15)
                                ->orderBy('name','ASC')
                                ->select('employees.name AS name','employees.num_employee AS num')
                                ->get();    
        }

        return view('incident.massiveindex')
                        ->with('employees', $employees)->with('incidents',$incidents);    
    }

    public function massiveStore(Request $request){
        $empleados = explode(",", $request->empleados);

        for( $i = 0 ; count($empleados) > $i ; $i++ ){
            $nombre = str_replace(array(";"), ',', $empleados[$i]);
            $insertar = DB::table('employees')
                        ->where('name','LIKE','%' .$nombre. '%')
                        ->get();

            $incident = new incident();
            $incident->is_external = false;
            $incident->cls_inc_id = 1;
            $incident->company_id = 0;
            $incident->num = 0;
            $incident->start_date = $request->start_date;
            $incident->end_date = $request->end_date;
            $incident->employee_id = $insertar[0]->id;
            $incident->type_incidents_id = $request->type_incidents_id;
            $incident->created_by = session()->get('user_id');
            $incident->updated_by = session()->get('user_id');
            $incident->save();

            $this->daysIncidents($incident->id,$incident->start_date,$incident->end_date,$incident->employee_id);
        }
        
        return redirect()->route('incidentes', [14])->with('mensaje', 'Incidente creado con éxito');  
    }
    /**
     * 
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reportIncidentsEmployeesStore(Request $request) {
        $incident = null;
        if (isset($request->id_incident) && $request->id_incident > 0) {
            $incident = incident::find($request->id_incident);
        }
        
        $incidentController = null;
        if (is_null($incident)) {
            $incident = new incident();
        }
        $incidentController = new incidentController();

        try {
            DB::transaction(function () use ($request, $incidentController, $incident) {
                $incident->type_incidents_id = $request->typeIncident;
                $incident->type_sub_inc_id = $request->type_sub_inc_id;

                $resp = SIncidentValidations::validateIncidentsAndHolidays($request->date, $request->date, $request->employee_id, $incident->id);
                if ($resp['status'] == 'error') {
                    throw new \Exception($resp['message']);
                }

                $incident->start_date = $request->date;
                $incident->end_date = $request->date;
                
                if ($incident->id > 0) {
                    $incident->update();
                }
                else {
                    $incident->is_external = false;
                    $incident->is_delete = 0;
                    $incident->cls_inc_id = 1;
                    $incident->employee_id = $request->employee_id;
                    $incident->created_by = session()->get('user_id');
                    $incident->updated_by = session()->get('user_id');
                    $idHolidayWorked = 0;
                    $incident = SIncidentValidations::manageIncident($incident, $request->comments, $idHolidayWorked);
                    
                    $incident->save();
                }

                $incidentController->saveDays($incident);

                if (isset($request->comments) && ! is_null($request->comments) && strlen($request->comments) > 0) {
                    $aAdjustIds = adjust_link::where('incident_id', $incident->id)
                                        ->join('prepayroll_adjusts AS adjs', 'adjust_link.adjust_id', '=', 'adjs.id')
                                        ->where('adjust_type_id', \SCons::PP_TYPES['COM'])
                                        ->select('adjust_id')
                                        ->get()
                                        ->toArray();

                    if (count($aAdjustIds) > 0) {
                        $lAdjusts = prepayrollAdjust::whereIn('id', $aAdjustIds)
                                        ->where('is_delete', 0)
                                        ->get();

                        foreach ($lAdjusts as $oAdjust) {
                            $oAdjust->comments = $request->comments;
                            $oAdjust->save();
                        }
                    }
                    else {
                        $adjust = new prepayrollAdjust();
                        $adjust->employee_id = $request->employee_id;
                        $adjust->dt_date = $request->date;
                        $adjust->minutes = 0;
                        $adjust->apply_to = 2;
                        $adjust->comments = $request->comments;
                        $adjust->is_delete = 0;
                        $adjust->is_external = 0;
                        $adjust->adjust_type_id = \SCons::PP_TYPES['COM'];
                        $adjust->apply_time = 0;
                        $adjust->created_by = session()->get('user_id');
                        $adjust->updated_by = session()->get('user_id');
                        $adjust->save();
    
                        $link = new adjust_link();
                        $link->adjust_id = $adjust->id;
                        $link->is_incident = 1;
                        $link->incident_id = $incident->id;
                        $link->save();
                    }
                }
            });
        }
        catch (\Throwable $e) {
            \Log::error($e);
            return redirect()->back()->with(['tittle' => 'Error', 'message' => $e->getMessage(), 'icon' => 'error']);
        }

        return redirect()->back()->with(['tittle' => 'Realizado', 'message' => 'Registro guardado con exito', 'icon' => 'success']);
    }

    /**
     * 
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reportIncidentsEmployeesDelete(Request $request){
        $incident = null;
        if (isset($request->id_incident) && $request->id_incident > 0) {
            $incident = incident::find($request->id_incident);
        }

        try {
            DB::transaction(function () use ($request, $incident) {
                if(! is_null($incident)) {
                    $incident->is_delete = 1;
                    $incident->update();
                    
                    $incidentController = new incidentController();
                    $incidentController->saveDays($incident);

                    $aAdjustIds = adjust_link::where('incident_id', $incident->id)
                                        ->join('prepayroll_adjusts AS adjs', 'adjust_link.adjust_id', '=', 'adjs.id')
                                        ->where('adjs.is_delete', 0)
                                        ->select('adjust_id')
                                        ->get()
                                        ->toArray();

                    if (count($aAdjustIds) > 0) {
                        prepayrollAdjust::whereIn('id', $aAdjustIds)
                                        ->update(['is_delete' => 1]);
                    }
                }
            });
        } catch (\Throwable $e) {
            return redirect()->back()->with(['tittle' => 'Error', 'message' => 'Error al guardar el registro', 'icon' => 'error']);
        }

        return redirect()->back()->with(['tittle' => 'Realizado', 'message' => 'Registro guardado con exito', 'icon' => 'success']);
    }
}
