<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SyncController;
use App\Models\adjust_link;
use App\Models\employees;
use App\Models\incident;
use App\Models\incidentDay;
use App\Models\IncidentExtSysLink;
use App\Models\prepayrollAdjust;
use App\SUtils\SDateUtils;
use App\SValidations\SIncidentValidations;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExternalIncidentsController extends Controller
{
    /**
     * Recibe la petición del sistema externo, procesa los datos y los guarda en la base de datos.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveIncident(Request $request)
    {
        $rules = [
            'ini_date' => 'required|date',
            'end_date' => 'required|date',
            'ext_key' => 'required|string',
            'ext_sys' => 'required|string',
            'folio' => 'nullable|string',
            'cls_inc_id' => 'required|integer|exists:class_incident,id',
            'type_inc_id' => 'required|integer|exists:type_incidents,id',
            'type_sub_inc_id' => 'nullable|integer|exists:type_sub_incidents,id_sub_incident',
            // ben_year
            // ben_ann
            'emp_comments' => 'nullable|string',
            'sup_comments' => 'nullable|string',
            'employee_id' => 'required|integer',
            'inc_dates' => 'required|array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->messages());
        }

        $ini_date = $request->input('ini_date');
        $end_date = $request->input('end_date');
        $ext_key = $request->input('ext_key');
        $ext_sys = $request->input('ext_sys');
        $folio = $request->input('folio');
        $cls_inc_id = $request->input('cls_inc_id');
        $type_inc_id = $request->input('type_inc_id');
        $type_sub_inc_id = $request->input('type_sub_inc_id');
        // $ben_year = $request->input('ben_year');
        // $ben_ann = $request->input('ben_ann');
        $emp_comments = !!$request->input('emp_comments') ? $request->input('emp_comments') : "";
        $sup_comments = !!$request->input('sup_comments') ? $request->input('sup_comments') : "";
        $employee_id = $request->input('employee_id');
        $inc_dates = $request->input('inc_dates');

        $oIncident = new incident();
        $oIncident->is_external = true;
        $oIncident->num = $folio;
        $oIncident->type_incidents_id = $type_inc_id;
        $oIncident->cls_inc_id = $cls_inc_id;
        $oIncident->type_sub_inc_id = $type_sub_inc_id;
        $oIncident->start_date = $ini_date;
        $oIncident->end_date = $end_date;
        $oIncident->ben_year = 0;
        $oIncident->ben_ann = 0;
        $oIncident->is_delete = 0;
        $oIncident->created_by = auth('api')->user()->id;
        $oIncident->updated_by = auth('api')->user()->id;
        $oIncident->created_at = date('Y-m-d H:i:s');
        $oIncident->updated_at = date('Y-m-d H:i:s');
        $oIncident->holiday_worked_id = 0;
        $comments = $sup_comments . (strlen($sup_comments) > 0 && strlen($emp_comments) > 0 ? " | " : "") . $emp_comments;

        try {
            // inicia transacción
            \DB::beginTransaction();
            
            // sincroniza con el ERP para asegurarse de tener los datos actualizados
            $config = \App\SUtils\SConfiguration::getConfigurations();
            $correcto = SyncController::syncronizeWithERP($config->lastSyncDateTime);
            
            // busca el empleado y en caso que no exista devuleve un error
            $oEmp = employees::where('external_id', $employee_id)->first();

            if (is_null($oEmp)) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => "No se encontró el empleado con la clave externa $employee_id",
                ]);
            }

            // valida que los días de incidente no se solapen con otras incidencias o días festivos
            $resp = SIncidentValidations::validateIncidentsAndHolidays($oIncident->start_date, $oIncident->end_date, $oEmp->id, 0);
            if ($resp['status'] == 'error') {
                return response()->json($resp);
            }
            
            $oIncident->employee_id = $oEmp->id;
            $oIncident->company_id = $oEmp->company_id;

            if (count($inc_dates) == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => "No se encontró información de días de incidencia",
                ]);
            }
            
            $lDays = collect($inc_dates)->where('taked', true);
            $oIncident->eff_day = count($lDays);

            $idHolidayWorked = 0;
            $oIncident = SIncidentValidations::manageIncident($oIncident, $comments, $idHolidayWorked);

            $oIncident->save();

            ExternalIncidentsController::saveDays($lDays, $oIncident);

            $aAdjustIds = adjust_link::where('incident_id', $oIncident->id)
                                    ->join('prepayroll_adjusts AS adjs', 'adjust_link.adjust_id', '=', 'adjs.id')
                                    ->where('adjs.is_delete', 0)
                                    ->select('adjust_id')
                                    ->get()
                                    ->toArray();

            if (count($aAdjustIds) > 0) {
                prepayrollAdjust::whereIn('id', $aAdjustIds)
                    ->update(['is_delete' => 1]);
            }

            $lDays = incidentDay::where('incidents_id', $oIncident->id)->get();

            foreach ($lDays as $day) {
                $adjust = new prepayrollAdjust();
                $adjust->employee_id = $oIncident->employee_id;
                $adjust->dt_date = $day->date;
                $adjust->minutes = 0;
                $adjust->apply_to = 2;
                $adjust->comments = strlen($comments) > 0 ? $comments : "Sistema externo";
                $adjust->is_delete = 0;
                $adjust->is_external = 0;
                $adjust->adjust_type_id = \SCons::PP_TYPES['COM'];
                $adjust->apply_time = 0;
                $adjust->created_by = auth('api')->user()->id;
                $adjust->updated_by = auth('api')->user()->id;
                $adjust->save();

                $link = new adjust_link();
                $link->adjust_id = $adjust->id;
                $link->is_incident = 1;
                $link->incident_id = $oIncident->id;
                $link->save();
            }

            $oLink = new IncidentExtSysLink();
            $oLink->incident_id = $oIncident->id;
            $oLink->external_key = $ext_key;
            $oLink->external_system = $ext_sys;

            $oLink->save();

            // termina transacción
            \DB::commit();

            return response()->json([
                'code' => 200,
                'message' => "OK",
            ]);
        }
        catch (\Throwable $th) {
            // cancela transacción
            \DB::rollBack();
            \Log::error($th);

            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Guarda los días de la incidencia.
     * 
     * @param \Illuminate\Support\Collection $aDays
     * @param incident $oIncident
     */
    public static function saveDays($lDays, $oIncident)
    {
        incidentDay::where('incidents_id', $oIncident->id)->delete();

        $aDays = [];
        $dayCounter = 1;
        foreach ($lDays as $day) {
            $oDay = new incidentDay();
            $oDay->incidents_id = $oIncident->id;
            $oDay->date = $day['date'];
            $oDay->num_day = $dayCounter;
            $oDay->is_delete = $oIncident->is_delete;

            $aDays[] = $oDay;
            $dayCounter++;
        }

        if (sizeof($aDays) > 0) {
            $oIncident->incidentDays()->saveMany($aDays);
        }
    }

    public function cancelIncidents(Request $request){
        $numEmployee = $request->input('num_employee');
        $incidentId = $request->input('incident_id');

        $employee = DB::table('employees')
                        ->where('num_employee',$numEmployee)
                        ->get();
        
        $incident = DB::table('incidents')
                        ->join('incident_ext_sys_links','incident_ext_sys_links.incident_id', '=', 'incidents.id')
                        ->where('incident_ext_sys_links.external_key',$incidentId)
                        ->where('is_delete',0)
                        ->get();
        
        if(count($incident) > 0){
            
            switch($employee[0]->way_pay_id){
                // Quincena
                case 1:
                    $quincenas = SDateUtils::getInfoDates($incident[0]->start_date,$incident[0]->end_date,$employee[0]->way_pay_id);

                    for( $i = 0 ; $i < count($quincenas) ; $i++ ){
                        $vobo = DB::table('prepayroll_report_emp_vobos')
                                    ->where('num_biweek',$quincenas[$i]->num)
                                    ->where('year',$quincenas[$i]->year)
                                    ->where('employee_id',$employee[0]->id)
                                    ->where('is_vobo',1)
                                    ->where('is_delete',0)
                                    ->get();
                        if( count($vobo) > 0 ){
                            return response()->json([
                                'code' => 500,
                                'message' => 'La incidencia ya tiene visto bueno no se puede borrar',
                            ]);
                        }
                    }
                    
                    DB::table('incidents')
                        ->where('id', $incident[0]->id)
                        ->update(['is_delete',1]);
                    
                    DB::table('incidents_day')
                        ->where('id', $incident[0]->id)
                        ->update(['is_delete',1]);

                    return response()->json([
                        'code' => 200,
                        'message' => 'La incidencia se borro con exito',
                    ]);

                    break;
                // Semana
                case 2:
                    $semanas = SDateUtils::getInfoDates($incident[0]->start_date,$incident[0]->end_date,$employee[0]->way_pay_id);
                    for( $i = 0 ; $i < count($semanas) ; $i++ ){

                        $vobo = DB::table('prepayroll_report_emp_vobos')
                                    ->where('num_week',$semanas[$i]->num)
                                    ->where('year',$semanas[$i]->year)
                                    ->where('employee_id',$employee[0]->id)
                                    ->where('is_vobo',1)
                                    ->where('is_delete',0)
                                    ->get();
                        if( count($vobo) > 0 ){
                            return response()->json([
                                'code' => 500,
                                'message' => 'La incidencia ya tiene visto bueno no se puede borrar',
                            ]);
                        }
                    }
                    
                    DB::table('incidents')
                        ->where('id', $incident[0]->id)
                        ->update(['is_delete',1]);
                    
                    DB::table('incidents_day')
                        ->where('id', $incident[0]->id)
                        ->update(['is_delete',1]);

                    return response()->json([
                        'code' => 200,
                        'message' => 'La incidencia se borro con exito',
                    ]);
                    break;    
            }
        }else{
            return response()->json([
                'code' => 550,
                'message' => 'La incidencia no existe',
            ]);
        }
    }

    
}
