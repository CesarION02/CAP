<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\SyncController;
use App\Models\employees;
use App\Models\incident;
use App\Models\incidentDay;
use App\Models\IncidentExtSysLink;
use App\SValidations\SIncidentValidations;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        $emp_comments = $request->input('emp_comments');
        $sup_comments = $request->input('sup_comments');
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
        $oIncident->created_by = 1;
        $oIncident->updated_by = 1;
        $oIncident->created_at = date('Y-m-d H:i:s');
        $oIncident->updated_at = date('Y-m-d H:i:s');
        $oIncident->holiday_worked_id = 0;
        $oIncident->comment = $emp_comments;

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
            $oIncident = SIncidentValidations::manageIncident($oIncident, $sup_comments, $idHolidayWorked);

            $oIncident->save();

            ExternalIncidentsController::saveDays($lDays, $oIncident);

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
}
