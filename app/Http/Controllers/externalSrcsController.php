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

    public function getData($startDate, $endDate, $aEmployeeIds, $payType)
    {
        $oHeader = new SDataHeader();
        /**
         * obtiene el número de empleado de cap por medio del id externo
         */
        $lEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('external_id', 'id');

        $lCapEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('id');

        $lCapPolEmployees = employees::whereIn('id', $lCapEmployees)
                                    ->pluck('ben_pol_id', 'id');

        /**
         * obtiene el reporte de retardos y horas extras
         */
        $report = SDelayReportUtils::processReport($startDate, $endDate, $payType, \SCons::REP_DELAY, $lCapEmployees);

        /**
         * Obtiene la query de las checadas
         */
        $qChecks = SDelayReportUtils::getRegistries($startDate, $endDate, 0, $lCapEmployees, false);

         /**
         * obtiene la query de los horarios
         */
        $lWorkshifts = SDelayReportUtils::getWorkshifts($startDate, $endDate, 0, $lCapEmployees);

        $oStartDate = Carbon::parse($startDate);
        $oEndDate = Carbon::parse($endDate);
        
        $lReport = collect($report);
        $lAuxReport = clone $lReport;
        $lReport = $lReport->where('delayMins', '>=', '0');
        $lcReport = clone $lReport;
        $lGrouped = $lcReport->groupBy('idEmployee')->map(function ($row) {
                    $registry = (object) [
                        'totalDelayMins' => $row->sum('delayMins'),
                    ];

            return $registry;
        });
        
        foreach ($lEmployees as $idEmployee => $extId) {
            $oRow = new SDataRow();
            $oRow->idEmployee = $extId;

            if ($lCapPolEmployees[$idEmployee] != \SCons::BEN_POL_FREE) {
                $oDate = clone $oStartDate;
                    /**
                 * crea un arreglo con los días a consultar
                 */
                while ($oDate->lessThanOrEqualTo($oEndDate)) {
                    $sDate = $oDate->toDateString();
                    $lChecks = clone $qChecks; // podría mejorarse a recorrer los días en lugar de hacer query por día
                    $lChecks = $lChecks->where('e.id', $idEmployee)
                                        ->where('r.date', $sDate)
                                        ->get();
                    
                    // si no tiene checadas:                                    
                    if (sizeof($lChecks) == 0) {
                        // checar incidencias ********************************************************
                        $lAbsences = prePayrollController::searchAbsence($idEmployee, $sDate);
                        
                        if (sizeof($lAbsences) == 0) {
                            $registry = (object) [
                                'date' => $sDate,
                                'time' => '12:00:00'
                            ];

                            /**
                             * Si no tiene checadas y no tiene incidencias se revisa que no sea un día inactivo para el empleado
                             * Si es un día activo se le pone falta
                             */
                            $result = SDelayReportUtils::getSchedule($startDate, $endDate, $idEmployee, $registry, clone $lWorkshifts, \SCons::REP_DELAY);
        
                            if ($result != null) {
                                if ($lCapPolEmployees[$idEmployee] == \SCons::BEN_POL_STRICT) {
                                    $oRow->absences++;
                                }
                            }
                        }
                    }
                    else {
                        $lColRep = collect($lAuxReport);
                        $lColRep = $lColRep->where('idEmployee', $idEmployee);
                        $lColRep = $lColRep->filter(function ($item) {
                                            // replace stristr with your choice of matching function
                                            return (stristr($item->comments, 'Falta Entrada') || stristr($item->comments, 'Falta Salida'))
                                                    && (! stristr($item->comments, 'Sin horario'));
                                        });

                        if (sizeof($lColRep) > 0) {
                            $oRow->hasNoChecks = true;

                            if ($lCapPolEmployees[$idEmployee] == \SCons::BEN_POL_STRICT) {
                                $oRow->lostBonus = true;
                            }
                        }
                    }

                    $oDate->addDay();
                }

                if (sizeof($lGrouped) > 0) {
                    if (isset($lGrouped{$idEmployee})) {
                        $oRow->delayMins = $lGrouped{$idEmployee}->totalDelayMins;
                    }
                }
            }

            $oHeader->rows[] = $oRow;
        }

        return $oHeader;
    }
}
