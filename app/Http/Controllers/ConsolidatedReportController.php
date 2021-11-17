<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\SUtils\SGenUtils;
use App\SUtils\SDelayReportUtils;
use App\SData\SConsolidatedRow;

class ConsolidatedReportController extends Controller
{
    public function showGenerationView()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $lEmployees = SGenUtils::toEmployeeIds(0, 0, []);

        return view('report.consolidated-report')->with('sTitle', 'Repote consolidado')
                                                ->with('sRoute', 'reporteConsolidado')
                                                ->with('lEmployees', $lEmployees)
                                                ->with('startOfWeek', $config->startOfWeek);
    }

    public function showReport(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;
        
        $oStartDate = Carbon::parse($sStartDate);
        $oEndDate = Carbon::parse($sEndDate);
        
        if (! $oStartDate->lessThanOrEqualTo($oEndDate)) {
            return \Redirect::back()->withErrors(['Error', 'La fecha de inicio debe ser previa a la fecha final']);
        }
        
        if ($request->optradio == "employee") {
            $iEmployee = $request->emp_id;

            if ($iEmployee > 0) {
                $lEmployees = SGenUtils::toEmployeeIds(0, 0, 0, [$iEmployee]);
                $payWay = $lEmployees[0]->way_pay_id;
            }
            else {
                return \Redirect::back()->withErrors(['Error', 'Debe seleccionar empleado']);
            }
        }
        else {
            /**
             * 1: quincena
             * 2: semana
             * 3: todos
             */
            $payWay = $request->pay_way == null ? \SCons::PAY_W_S : $request->pay_way;

            $filterType = $request->i_filter;
            $ids = $request->elems;
            $lEmployees = SGenUtils::toEmployeeIds($payWay, $filterType, $ids);
        }

        return $this->getConsolidatedReport($lEmployees, $payWay, $oStartDate, $oEndDate);
    }

    public function getConsolidatedReport($lEmployees, $payWay, $oStartDate, $oEndDate)
    {
        $lConsolidated = [];
        $oDate = clone $oStartDate;

        /**
         * crea un arreglo con los días a consultar
         */
        while ($oDate->lessThanOrEqualTo($oEndDate)) {
            $aDates[] = clone $oDate;
            $oDate->addDay();
        }

        $qWorkshifts = SDelayReportUtils::getWorkshifts($oStartDate->toDateString(), $oEndDate->toDateString(), $payWay, []);

        /**
         * Hace un recorrido del arreglo de empleados
         */
        foreach ($lEmployees as $oEmployee) {
            $oRow = new SConsolidatedRow();
            $oRow->employee = $oEmployee;

            $lDates = [];
            foreach ($aDates as $oDt) {
                $oColDate = (object) [];

                $registry = $registry = (object) [
                                'date' => $oDt->toDateString(),
                                'time' => '12:00:00',
                                'type_id' => 1
                            ];
                $oColDate->schedule = SDelayReportUtils::getSchedule($oStartDate->toDateString(), $oEndDate->toDateString(), $oEmployee->id, $registry, clone $qWorkshifts, \SCons::REP_HR_EX);
                if ($oColDate->schedule != null) {
                    if ($oColDate->schedule->auxWorkshift != null) {
                        $oColDate->s_name = $oColDate->schedule->auxWorkshift->name;
                        $oColDate->in_time = $oColDate->schedule->auxWorkshift->entry;
                        $oColDate->out_time = $oColDate->schedule->auxWorkshift->departure;
                    }
                    else if ($oColDate->schedule->auxScheduleDay != null) {
                        $oColDate->s_name = $oColDate->schedule->auxScheduleDay->template_name;
                        $oColDate->in_time = $oColDate->schedule->auxScheduleDay->entry;
                        $oColDate->out_time = $oColDate->schedule->auxScheduleDay->departure;

                    }
                }

                $lDates[$oDt->toDateString()] = $oColDate;
            }

            $oRow->lDates = $lDates;

            $lConsolidated[] = $oRow;
        }

        // dd($lConsolidated);

        return view('report.consolidated-report-result')->with('lConsolidated', $lConsolidated)
                                                        ->with('aDates', $aDates)
                                                        ->with('sStartDate', $oStartDate->format("d M Y"))
                                                        ->with('sEndDate', $oEndDate->format("d M Y"));
    }
}
