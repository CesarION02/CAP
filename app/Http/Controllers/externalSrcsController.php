<?php

namespace App\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\SUtils\SInfoWithPolicy;
use App\SUtils\SDataHeader;
use App\SUtils\SDataRow;
use App\Models\employees;
use App\SUtils\SGenUtils;

class externalSrcsController extends Controller
{
    public function __construct() {
        // Códigos de respuesta:
        $this->OK = "200";
        $this->ERROR = "500"; //  Error del servidor
        $this->NOT_VOBO = "550"; // Nómina no autorizada
    }

    public function getAbsDelays(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'employees' => 'required',
            'pay_type' => 'required'
        ]);

        if (! $validator->passes()) {
            // return response()->json(['success'=>'Added new records.']);
            return response()->json(['error' => $validator->errors()->all()]);
        }

        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $aEmployeeIds = $request->employees;
            $payType = $request->pay_type == "1" ? \SCons::PAY_W_S : \SCons::PAY_W_Q;

            if (is_string($aEmployeeIds)) {
                $aEmployeeIds = explode(",", $aEmployeeIds);
            }

            $oJAbsDelays = $this->getInfo($startDate, $endDate, $aEmployeeIds, $payType);
        }
        catch (\Throwable $e) {
            $response = (object) [
                "code" => $this->ERROR,
                "data" => $e->getMessage()
            ];

            return json_encode($response, JSON_PRETTY_PRINT);
        }

        $response = (object) [
            "code" => $this->OK,
            "data" => $oJAbsDelays
        ];

        return json_encode($response, JSON_PRETTY_PRINT);
    }

    public function getInfo($startDate, $endDate, $aEmployeeIds, $payType) {
        $lCapEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('id');

        $lEmployees = SGenUtils::toEmployeeIds($payType, 0, null, $lCapEmployees);

        // $lRows = SDataProcess::process($startDate, $endDate, $payType, $lEmployees);
        // $cReport = collect($lRows);

        $oDate = Carbon::parse($startDate);

         /**
         * Obtiene el reporte de horas extra, que contiene también domingos y festivos.
         */
        $info = SInfoWithPolicy::preProcessInfo($startDate, $oDate->year, $endDate, $payType);
        $lExtras = \DB::table('processed_data')
                                ->join('employees','employees.id','=','processed_data.employee_id')
                                ->whereIn('employees.id', $lCapEmployees)
                                ->where(function($query) use ($startDate, $endDate) {
                                    $query->whereBetween('inDate',[$startDate, $endDate])
                                    ->OrwhereBetween('outDate',[$startDate, $endDate]);
                                })
                                ->get();
        $cReport = collect($lExtras);

        $oHeader = new SDataHeader();

        $cData = clone $cReport;
        $lGrouped = $cData->groupBy('employee_id')->map(function ($row) {
                                $registry = (object) [
                                    'totalDelayMins' => $row->sum('delayMins'),
                                ];

                        return $registry;
                    });

        foreach ($lEmployees as $oEmployee) {
            $oRow = new SDataRow();
            $oRow->idEmployee = $oEmployee->external_id;

            if ($oEmployee->ben_pol_id == \SCons::BEN_POL_FREE) {
                $oHeader->rows[] = $oRow;
                continue;
            }

            if (sizeof($lGrouped) > 0) {
                if (isset($lGrouped{$oEmployee->id})) {
                    $oRow->delayMins = $lGrouped{$oEmployee->id}->totalDelayMins;
                }
            }

            $cData1 = clone $cReport;

            $counted = $cData1->where('employee_id', $oEmployee->id);
            $counted = $counted->where('hasabsence', true);

            if (sizeof($counted) > 0) {
                $oRow->absences = sizeof($counted);
            }

            $lAuxReport = clone $cReport;
            $lColRep = collect($lAuxReport);
                        $lColRep = $lColRep->where('employee_id', $oEmployee->id);
                        $lColRep = $lColRep->filter(function ($item) {
                                            return (stristr($item->comments, 'Sin entrada') || stristr($item->comments, 'Sin salida'))
                                                    && (! stristr($item->comments, 'Sin horario'));
                                        });
                        // $lColRep = $lColRep->filter(function ($item) {
                        //                 return (! $item->hasCheckOut || ! $item->hasCheckIn);
                        //             });

            if (sizeof($lColRep) > 0) {
                $oRow->hasNoChecks = true;

                if ($oEmployee->ben_pol_id == \SCons::BEN_POL_STRICT) {
                    $oRow->lostBonus = true;
                }
            }

            if (! $oRow->lostBonus) {
                $oDateIndex = Carbon::parse($startDate);
                $oDateEnd = Carbon::parse($endDate);

                while ($oDateIndex->lessThanOrEqualTo($oDateEnd)) {
                    $lAbsences = prePayrollController::searchAbsence($oEmployee->id, $oDateIndex->toDateString());
                        
                    if (sizeof($lAbsences) > 0) {
                        $lAbs = collect($lAbsences);
                        // Revisa si la incidencia es permitida, si no, pierde el bono
                        $lAbs = $lAbs->whereNotIn('type_id', [3, 7, 12, 14, 15, 16]);

                        if (sizeof($lAbs) > 0) {
                            $oRow->lostBonus = true;
                            // $oRow->hasAbss = true;
                            break;
                        }
                    }

                    $oDateIndex->addDay();
                }
            }

            $oHeader->rows[] = $oRow;
        }

        return $oHeader;
    }

}
