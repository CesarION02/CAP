<?php namespace App\STasks;
      use App\Models\employees;
      use App\Models\prepayrollAdjust;
      use App\Models\prepayrollAdjustExtLink;
      use App\SData\SDataProcess;
      use App\SReport\SJourneyReport;
      use App\SUtils\SGenUtils;
      use Carbon\Carbon;

class SAdjustsPgh {
    
    /**
     * Undocumented function
     *
     * @param string $jsonCfg
     * 
     * @return string
     */
    public static function processAdjustTask(string $jsonCfg)
    {
        try {
            // Validar si la cadena recibida es un JSON
            if (! SJourneyReport::isJson($jsonCfg)) {
                return "Error, la configuración recibida no es un string JSON.";
            }

            $oJson = json_decode($jsonCfg);
            $oAdjPgh = new prepayrollAdjust((array) $oJson->oAdj);

            $oEmp = employees::find($oAdjPgh->employee_id);
            $lEmployees = SGenUtils::toEmployeeIds($oEmp->way_pay_id, 0, [], [$oEmp->id], 0);

            $exists = prepayrollAdjustExtLink::where('external_key', $oJson->oLink->external_key)
                                    ->where('external_system', $oJson->oLink->external_system)
                                    ->get();

            if (count($exists) > 0) {
                return "Ya existe la llave externa $oJson->oLink->external_system - $oJson->oLink->external_key";
            }

            // Se toma en cuenta un rango de tres días hacia atrás y 3 hacia delante
            $oStartDate = Carbon::parse($oAdjPgh->dt_date)->subDays(3);
            $oEndDate = Carbon::parse($oAdjPgh->dt_date)->addDays(3);
            $lRows = SDataProcess::process($oStartDate->toDateString(), $oEndDate->toDateString(), $oEmp->way_pay_id, $lEmployees);

            $time = null;
            switch ($oAdjPgh->adjust_type_id) {
                case \SCons::PP_TYPES['JE']:
                case \SCons::PP_TYPES['JS']:
                case \SCons::PP_TYPES['OF']:
                case \SCons::PP_TYPES['DHE']:
                case \SCons::PP_TYPES['AHE']:
                case \SCons::PP_TYPES['COM']:
                    return  "El tipo de ajuste recibido no es válido";

                /**
                 * Justificar retardo
                 */
                case \SCons::PP_TYPES['OR']:
                    $lFlRows = $lRows->where('inDate', $oAdjPgh->dt_date);
                    if (count($lFlRows) > 0) {
                        $oRow = $lFlRows->first();
                        if (strlen($oRow->inDateTime) > 12) {
                            $time = Carbon::parse($oRow->inDateTime)->toTimeString();
                        }
                        else {
                            return "El empleado no tiene entrada registrada para la fecha $oAdjPgh->dt_date";
                        }
                    }
                    else {
                        return "El empleado no tiene entrada registrada para la fecha $oAdjPgh->dt_date";
                    }

                    break;

                /**
                 * Justificar salida anticipada
                 */
                case \SCons::PP_TYPES['JSA']:
                    $lFlRows = $lRows->where('outDate', $oAdjPgh->dt_date);

                    if (count($lFlRows) > 0) {
                        $oRow = $lFlRows->first();
                        if (strlen($oRow->outDateTime) > 12) {
                            $time = Carbon::parse($oRow->outDateTime)->toTimeString();
                        }
                        else {
                            return "El empleado no tiene salida registrada para la fecha $oAdjPgh->dt_date";
                        }
                    }
                    else {
                        return "El empleado no tiene salida registrada para la fecha $oAdjPgh->dt_date";
                    }

                    break;
                
                default:
                    # code...
                    break;
            }

            if (is_null($time)) {
                return "Error al determinar la hora del ajuste";
            }

            // inicia transacción
            \DB::beginTransaction();

            $oAdjPgh->dt_time = $time;
            $oAdjPgh->save();

            $oLink = new prepayrollAdjustExtLink((array) $oJson->oLink);
            $oLink->prepayroll_adjust_id = $oAdjPgh->id;

            $oLink->save();

            // termina transacción
            \DB::commit();

            return "";
        }
        catch (\Throwable $th) {
            // rollback
            \DB::rollback();
            
            \Log::error($th);
            return $th->getMessage();
        }
    }
}
