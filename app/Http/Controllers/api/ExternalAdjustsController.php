<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\SyncController;
use App\Models\employees;
use App\Models\prepayrollAdjust;
use App\Models\prepayrollAdjustExtLink;
use App\Models\ProgrammedTask;
use App\SData\SDataProcess;
use App\SUtils\SDateUtils;
use App\SUtils\SGenUtils;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ExternalAdjustsController extends Controller
{
    public function saveAdjust(Request $request)
    {
        $rules = [
            'employee_id' => 'required|integer',
            'dt_date' => 'required|date',
            'minutes' => 'required|integer',
            'comments' => 'nullable|string',
            'ext_key' => 'required|string',
            'ext_sys' => 'required|string',
            'adjust_type_id' => 'required|integer|exists:prepayroll_adjusts,id'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->messages());
        }

        $employee_id = $request->input('employee_id');
        $dt_date = $request->input('dt_date');
        $minutes = $request->input('minutes');
        $comments = $request->input('comments');
        $ext_key = $request->input('ext_key');
        $ext_sys = $request->input('ext_sys');
        $adjust_type_id = $request->input('adjust_type_id');

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
                                            'status' => "Error",
                                            'message' => "No se encontró el empleado con la clave externa $employee_id",
                                            'data' => null
                                        ], 404);
            }

            $exists = prepayrollAdjustExtLink::where('external_key', $ext_key)
                                    ->where('external_system', $ext_sys)
                                    ->get();

            if (count($exists) > 0) {
                return response()->json([
                    'status' => "Error",
                    'message' => "Ya existe la llave externa $ext_sys - $ext_key",
                    'data' => null
                ], 400);
            }

            $lEmployees = SGenUtils::toEmployeeIds($oEmp->way_pay_id, 0, [], [$oEmp->id], 0);
            // Se toma en cuenta un rango de tres días hacia atrás y 3 hacia delante
            $oStartDate = Carbon::parse($dt_date)->subDays(3);
            $oEndDate = Carbon::parse($dt_date)->addDays(3);
            $lRows = SDataProcess::process($oStartDate->toDateString(), $oEndDate->toDateString(), $oEmp->way_pay_id, $lEmployees);

            $time = "";
            $applyTo = 0;
            $applyTime = 0;
            $bForLater = false;
            switch ($adjust_type_id) {
                case \SCons::PP_TYPES['JE']:
                case \SCons::PP_TYPES['JS']:
                case \SCons::PP_TYPES['OF']:
                case \SCons::PP_TYPES['DHE']:
                case \SCons::PP_TYPES['AHE']:
                case \SCons::PP_TYPES['COM']:
                    return response()->json([
                                            'message' => "El tipo de ajuste recibido no es válido",
                                            'status' => "Error",
                                            'data' => null
                                        ], 400);

                /**
                 * Justificar retardo
                 */
                case \SCons::PP_TYPES['OR']:
                    $lFlRows = $lRows->where('inDate', $dt_date);
                    if (count($lFlRows) > 0) {
                        $oRow = $lFlRows->first();
                        if (strlen($oRow->inDateTime) > 12) {
                            $time = Carbon::parse($oRow->inDateTime)->toTimeString();
                        }
                    }

                    if (strlen($time) == 0) {
                        // Si la fecha recibida es mayor a la fecha actual el ajuste se "guarda para después"
                        $oNow = Carbon::now(new \DateTimeZone('-6:00'));
                        if ($oNow->toDateString() > $dt_date) {
                            return response()->json([
                                                        'data' => null,
                                                        'status' => 'Error',
                                                        'message' => "El empleado no tiene entrada registrada para la fecha ". Carbon::parse($dt_date)->format('d-m-Y') ." (no checo)",
                                                    ], 200);
                        }
                        $bForLater = true;
                    }

                    $applyTo = 1;
                    $applyTime = true;
                    break;

                /**
                 * Justificar salida anticipada
                 */
                case \SCons::PP_TYPES['JSA']:
                    $lFlRows = $lRows->where('outDate', $dt_date);

                    if (count($lFlRows) > 0) {
                        $oRow = $lFlRows->first();
                        if (strlen($oRow->outDateTime) > 12) {
                            $time = Carbon::parse($oRow->outDateTime)->toTimeString();
                        }
                    }

                    if (strlen($time) == 0) {
                        // Si la fecha recibida es mayor a la fecha actual el ajuste se "guarda para después"
                        $oNow = Carbon::now(new \DateTimeZone('-6:00'));
                        if ($oNow->toDateString() > $dt_date) {
                            return response()->json([
                                                    'status' => 'Error',
                                                    'message' => "El empleado no tiene salida registrada para la fecha ". Carbon::parse($dt_date)->format('d-m-Y') ." (no checo)",
                                                    'data' => null
                                                ],200);
                        }
                        $bForLater = true;
                    }

                    $applyTo = 2;
                    $applyTime = true;
                    break;
                
                default:
                    # code...
                    break;
            }

            $oAdjust = new prepayrollAdjust();
            $oAdjust->employee_id = $oEmp->id;
            $oAdjust->dt_date = $dt_date;
            $oAdjust->dt_time = $time;
            $oAdjust->minutes = $minutes;
            $oAdjust->apply_to = $applyTo;
            $oAdjust->comments = is_null($comments) ? "" : $comments;
            $oAdjust->is_delete = false;
            $oAdjust->is_external = true;
            $oAdjust->adjust_type_id = $adjust_type_id;
            $oAdjust->apply_time = $applyTime;
            $oAdjust->created_by = auth('api')->user()->id;
            $oAdjust->updated_by = auth('api')->user()->id;

            // solo si se encontró una hora para amarrar el ajuste se guarda
            if (! $bForLater) {
                $oAdjust->save();
            }

            $oLink = new prepayrollAdjustExtLink();
            $oLink->external_key = $ext_key;
            $oLink->external_system = $ext_sys;
            $oLink->prepayroll_adjust_id = $oAdjust->id;

            // solo si se encontró una hora para amarrar el ajuste se guarda
            if (! $bForLater) {
                $oLink->save();
            }

            if ($bForLater) {
                // se convierten el objeto del ajuste y del link en json para almacenarse en la BD
                $oJson = new \stdClass();
                $oJson->oAdj = $oAdjust;
                $oJson->oLink = $oLink;
                $dateTime = Carbon::createFromFormat('Y-m-d', $dt_date, new \DateTimeZone('-6:00'))->addDay();

                // se programa la tarea un día después de la fecha del ajuste
                $oTask = new ProgrammedTask();
                $oTask->execute_on = $dateTime->toDateString();
                $oTask->apply_time = false;
                $oTask->cfg = json_encode($oJson, JSON_PRETTY_PRINT);
                $oTask->reference_id = $ext_key;
                $oTask->is_done = false;
                $oTask->is_delete = false;
                $oTask->task_type_id = \SCons::TASK_TYPE_ADJUST_PGH;

                $oTask->save();
            }

            // termina transacción
            \DB::commit();

            return response()->json([
                                        'status' => 'Success',
                                        'message' => "Se ha registrado el ajuste correctamente",
                                        'data' => null
                                    ], 200);
        }
        catch (\Throwable $th) {
            // rollback
            \DB::rollback();
            // log
            \Log::error($th);

            return response()->json([
                                        'status' => 'Error',
                                        'message' => $th->getMessage(),
                                        'data' => null
                                    ], 500);
        }
    }

    public function cancelAdjust(Request $request){
        $numEmployee = $request->input('num_employee');
        $adjustId = $request->input('adjust_id'); 
        
        $employee = DB::table('employees')
                        ->where('num_employee',$numEmployee)
                        ->get();
        // revisar si se tiene una tarea programada                 
        $programmedTask = DB::table('programmed_tasks')
                        ->where('reference_id', $adjustId)
                        ->where('is_delete', 0)
                        ->where('is_done', 0)
                        ->get();

        if( count($programmedTask) > 0){
            
            DB::table('programmed_tasks')
                ->where('id_task', $programmedTask[0]->id_task)
                ->update(['is_delete' => 1]);

            return response()->json([
                'code' => 200,
                'message' => 'El permiso fue borrado con éxito',
            ]);

        }else{
            $adjust = DB::table('prepayroll_adjusts')
                        ->join('prepayroll_adjusts_ext_links','prepayroll_adjusts_ext_links.prepayroll_adjust_id', '=', 'prepayroll_adjusts.id')
                        ->where('prepayroll_adjusts_ext_links.external_key',$adjustId)
                        ->where('is_delete',0)
                        ->get();
        
            if(count($adjust) > 0){
                
                switch($employee[0]->way_pay_id){
                // Quincena
                    case 1:
                        $quincenas = SDateUtils::getInfoDates($adjust[0]->dt_date,$adjust[0]->dt_date,$employee[0]->way_pay_id);
                    
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
                                    'message' => 'El permiso ya tiene visto bueno no se puede borrar',
                                ]);
                            }
                        }
                                        
                        DB::table('prepayroll_adjusts')
                            ->where('id', $adjust[0]->prepayroll_adjust_id)
                            ->update(['is_delete' => 1]);

                                        
                        return response()->json([
                            'code' => 200,
                            'message' => 'El permiso se borro con exito',
                        ]);
                    
                        break;
                        // Semana
                    case 2:
                        $semanas = SDateUtils::getInfoDates($adjust[0]->start_date,$adjust[0]->end_date,$employee[0]->way_pay_id);
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
                                    'message' => 'El ajuste ya tiene visto bueno no se puede borrar',
                                ]);
                            }
                        }
                                        
                        DB::table('prepayroll_adjust')
                            ->where('id', $adjust[0]->id)
                            ->update(['is_delete' => 1]);
                    
                        return response()->json([
                            'code' => 200,
                            'message' => 'El permiso se borro con exito',
                        ]);
                        break;    
                }
            }else{
                return response()->json([
                    'code' => 550,
                    'message' => 'El permiso no existe',
                ]);
            }
        }

        
    }
}
