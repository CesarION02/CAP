<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\incident;
use App\Models\incidentDay;
use App\SUtils\SDelayReportUtils;
use App\Models\typeincident;
use App\Models\employees;
use App\Models\company;
use App\Http\Requests\ValidacionTypeincident;
use App\SUtils\SPrepayrollAdjustUtils;
use DB;

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
                    ->with('start_date', $start_date)
                    ->with('end_date', $end_date);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($incidentType = 0)
    {
        $incidents = typeincident::orderBy('name','ASC');

        if ($incidentType > 0) {
            $incidents = $incidents->where('is_agreement', 1);
        }

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
                                ->select('employees.name AS name','employees.id AS num', 'employees.num_employee')
                                ->get();
        }else{
            $employees = DB::table('employees')
                                ->join('departments','departments.id','=','employees.department_id')
                                ->where('is_active', true)
                                ->where('department_id',15)
                                ->orderBy('name','ASC')
                                ->select('employees.name AS name','employees.id AS num', 'employees.num_employee')
                                ->get();    
        }

        return view('incident.create')
                        ->with('incidentType', $incidentType)
                        ->with('incidents', $incidents)
                        ->with('employees', $employees);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $incidents = DB::table('incidents')
                ->where('employee_id','=',$request->employee_id)
                ->whereIn('incidents.start_date',[$request->start_date,$request->end_date])
                ->get();
        if(count($incidents) == 0){
            $incident = new incident($request->all());
            $incident->external_key = "0_0";
            $incident->cls_inc_id = 1;
            $incident->created_by = session()->get('user_id');
            $incident->updated_by = session()->get('user_id');


            $incident->save();

            $this->daysIncidents($incident->id,$incident->start_date,$incident->end_date,$incident->employee_id);

            if ($request->incident_type > 0) {
                return redirect()->route('incidentes', [$request->incident_type])->with('mensaje', 'Incidente creado con éxito');
            }
            else {
                return redirect('incidents')->with('mensaje', 'Incidente creado con éxito');
            }
        }else{
            return redirect('/incidents/14')->with('mensaje','Ya existe un incidente para esta fecha');
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
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $incidents = typeincident::where('is_agreement', 1)->orderBy('name','ASC')->pluck('id','name');
        $datas = DB::table('incidents')
                        ->join('employees','employees.id',"=","incidents.employee_id")
                        ->where('incidents.id',$id)
                        ->select('incidents.id AS id','employees.name AS name','incidents.start_date AS ini','incidents.end_date AS fin')
                        ->get();
        return view('incident.edit', compact('datas'))->with('incidents',$incidents);
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
                                '1_8' => '16',
                                '1_9' => '18',
                                '1_10' => '20',
                            ];
        
        foreach ($lAbsences as $jAbs) {
            $this->employees = employees::select('id', 'external_id')
                            ->pluck('id', 'external_id');
            $lCapAbss = incident::select('incidents.id AS idincident', 'external_key','companies.id AS idcompany')
                                ->join('companies','companies.id','=','incidents.company_id')
                                ->where('external_key', "".$jAbs->id_emp."_".$jAbs->id_abs."")
                                ->where('companies.db_name',$jAbs->company)
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
     * @param [type] $jAbs
     * @return void
     */
    private function insertIncident($jAbs, $company)
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
        $abs->company_id = $company;

        $abs->save();

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
        while ($oDate->lessThanOrEqualTo($oEndDate) && $dayCounter <= $oIncident->eff_day) {
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

                    if ($result == null || ($result->auxScheduleDay != null && !$result->auxScheduleDay->is_active)) {
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
            $incident->external_key = "0_0";
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

}
