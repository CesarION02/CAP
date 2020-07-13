<?php

namespace App\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SDataHeader;
use App\SUtils\SDataRow;
use App\Models\employees;
use App\Http\Controllers\prePayrollController;
use App\SUtils\SGenUtils;
use App\SData\SDataProcess;

class externalSrcsController extends Controller
{
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

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $aEmployeeIds = $request->employees;
        $payType = $request->pay_type == "1" ? \SCons::PAY_W_S : \SCons::PAY_W_Q;

        if (is_string($aEmployeeIds)) {
            $aEmployeeIds = explode(",", $aEmployeeIds);
        }

        $oJAbsDelays = $this->getInfo($startDate, $endDate, $aEmployeeIds, $payType);

        return json_encode($oJAbsDelays, JSON_PRETTY_PRINT);
    }

    public function getInfo($startDate, $endDate, $aEmployeeIds, $payType) {
        $lCapEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('id');

        $lEmployees = SGenUtils::toEmployeeIds($payType, 0, null, $lCapEmployees);

        $lRows = SDataProcess::process($startDate, $endDate, $payType, $lEmployees);
        $cReport = collect($lRows);

        $oHeader = new SDataHeader();

        $cData = clone $cReport;
        $lGrouped = $cData->groupBy('idEmployee')->map(function ($row) {
                                $registry = (object) [
                                    'totalDelayMins' => $row->sum('entryDelayMinutes'),
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

            $counted = $cData1->where('idEmployee', $oEmployee->id);
            $counted = $counted->where('hasAbsence', true);

            if (sizeof($counted) > 0) {
                $oRow->absences = sizeof($counted);
            }

            $lAuxReport = clone $cReport;
            $lColRep = collect($lAuxReport);
                        $lColRep = $lColRep->where('idEmployee', $oEmployee->id);
                        $lColRep = $lColRep->filter(function ($item) {
                                            // replace stristr with your choice of matching function
                                            return (stristr($item->comments, 'Sin entrada') || stristr($item->comments, 'Sin salida'))
                                                    && (! stristr($item->comments, 'Sin horario'));
                                        });

            if (sizeof($lColRep) > 0) {
                $oRow->hasNoChecks = true;

                if ($oEmployee->ben_pol_id == \SCons::BEN_POL_STRICT) {
                    $oRow->lostBonus = true;
                }
            }

            $oHeader->rows[] = $oRow;
        }

        return $oHeader;
    }

}
