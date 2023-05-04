<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\SyncController;
use App\Models\employees;
use App\Models\prepayrollAdjust;
use App\Models\prepayrollAdjustExtLink;
use App\SData\SDataProcess;
use App\SUtils\SGenUtils;
use Carbon\Carbon;
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
                    'code' => 404,
                    'status' => 'error',
                    'message' => "No se encontró el empleado con la clave externa $employee_id",
                ]);
            }

            $lEmployees = SGenUtils::toEmployeeIds($oEmp->way_pay_id, 0, [], [$oEmp->id], 0);
            // Se toma en cuenta un rango de tres días hacia atrás y 3 hacia delante
            $oStartDate = Carbon::parse($dt_date)->subDays(3);
            $oEndDate = Carbon::parse($dt_date)->addDays(3);
            $lRows = SDataProcess::process($oStartDate->toDateString(), $oEndDate->toDateString(), $oEmp->way_pay_id, $lEmployees);

            $time = "";
            $applyTo = 0;
            $applyTime = 0;
            switch ($adjust_type_id) {
                case \SCons::PP_TYPES['JE']:
                case \SCons::PP_TYPES['JS']:
                case \SCons::PP_TYPES['OF']:
                case \SCons::PP_TYPES['DHE']:
                case \SCons::PP_TYPES['AHE']:
                case \SCons::PP_TYPES['COM']:
                    return response()->json([
                        'code' => 400,
                        'status' => 'error',
                        'message' => "El tipo de ajuste recibido no es válido",
                    ]);

                case \SCons::PP_TYPES['OR']:
                    $lFlRows = $lRows->where('inDate', $dt_date);
                    if (count($lFlRows) > 0) {
                        $oRow = $lFlRows->first();
                        if (strlen($oRow->inDateTime) > 12) {
                            $time = Carbon::parse($oRow->inDateTime)->toTimeString();
                        }
                        else {
                            return response()->json([
                                'code' => 400,
                                'status' => 'error',
                                'message' => "El empleado no tiene entrada registrada para la fecha $dt_date",
                            ]);
                        }
                    }
                    else {
                        return response()->json([
                            'code' => 400,
                            'status' => 'error',
                            'message' => "El empleado no tiene entrada registrada para la fecha $dt_date",
                        ]);
                    }

                    $applyTo = 1;
                    $applyTime = true;
                    break;

                case \SCons::PP_TYPES['JSA']:
                    $lFlRows = $lRows->where('outDate', $dt_date);

                    if (count($lFlRows) > 0) {
                        $oRow = $lFlRows->first();
                        if (strlen($oRow->outDateTime) > 12) {
                            $time = Carbon::parse($oRow->outDateTime)->toTimeString();
                        }
                        else {
                            return response()->json([
                                'code' => 400,
                                'status' => 'error',
                                'message' => "El empleado no tiene salida registrada para la fecha $dt_date",
                            ]);
                        }
                    }
                    else {
                        return response()->json([
                            'code' => 400,
                            'status' => 'error',
                            'message' => "El empleado no tiene salida registrada para la fecha $dt_date",
                        ]);
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

            $oAdjust->save();

            $oLink = new prepayrollAdjustExtLink();
            $oLink->prepayroll_adjust_id = $oAdjust->id;
            $oLink->external_key = $ext_key;
            $oLink->external_system = $ext_sys;

            $oLink->save();

            // termina transacción
            \DB::commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => "Se ha registrado el ajuste correctamente",
            ]);
        }
        catch (\Throwable $th) {
            // rollback
            \DB::rollback();
            // log
            \Log::error($th);

            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }

    }
}
