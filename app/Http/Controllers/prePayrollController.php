<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\SUtils\SDelayReportUtils;
use App\Models\employees;
use App\Models\incident;
use App\SPayroll\SPrePayroll;
use App\SPayroll\SPrePayrollRow;
use App\SPayroll\SPrePayrollDay;

class prePayrollController extends Controller
{
    /**
     * Retorna un JSON con la información de la prenómina
     *
     * @param Request $request
     * 
     * @return JSON string 
     * {
     *	start_date: date,
     *	end_date: date,
     *	rows: [
     *			{
     *			employee_id: integer,
     *			days: [
     *				{
     *				dt_date: date,
     *				entry: time,
     *				leave: time,
     *				prog_entry: time,
     *				prog-leave: time,
     *				abs_id: integer,
     *				type_abs_id: integer,
     *				holiday_id: integer
     *				}
     *				]
     *			},
     *		]
     * }
     */
    public function getPrePayroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'employees' => 'required'
        ]);

        if (!$validator->passes()) {
            // return response()->json(['success'=>'Added new records.']);
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $aEmployeeIds = $request->employees;

        if (is_string($aEmployeeIds)) {
            $aEmployeeIds = explode(",", $aEmployeeIds);
        }

        $oPrepayroll = $this->makePrepayroll($startDate, $endDate, $aEmployeeIds);

        return json_encode($oPrepayroll, JSON_PRETTY_PRINT);
    }

    /**
     * Obtiene la prenómina consultando días entre fechas, empleados, horarios y checadas
     *
     * @param String $startDate
     * @param String $endDate
     * @param array $aEmployeeIds
     * 
     * @return SPrePayroll object
     */
    private function makePrepayroll($startDate, $endDate, $aEmployeeIds)
    {
        $aDates = [];
        $oStartDate = Carbon::parse($startDate);
        $oEndDate = Carbon::parse($endDate);
        $oDate = clone $oStartDate;

        /**
         * crea un arreglo con los días a consultar
         */
        while ($oDate->lessThanOrEqualTo($oEndDate)) {
            $aDates[] = $oDate->toDateString();
            $oDate->addDay();
        }

        /**
         * obtiene el número de empleado de cap por medio del id externo
         */
        $lEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('external_id', 'id');

        $lCapEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('id');

        /**
         * Obtiene la query de las checadas
         */
        $qChecks = SDelayReportUtils::getRegistries($startDate, $endDate, 0, $lCapEmployees, false);
        
        /**
         * obtiene la query de los horarios
         */
        $lWorkshifts = SDelayReportUtils::getWorkshifts($startDate, $endDate, 0, $lCapEmployees);

        $prePayroll = new SPrePayroll();
        $prePayroll->start_date = $startDate;
        $prePayroll->end_date = $endDate;
        $prePayroll->rows = [];

        foreach ($lEmployees as $idEmployee => $extId) {
            $row = new SPrePayrollRow();
            $row->employee_id = $extId;
            $row->days = [];

            foreach ($aDates as $sDate) {
                $day = new SPrePayrollDay();
                $day->dt_date = $sDate;

                $day->absences = [];
                $lChecks = clone $qChecks; // podría mejorarse a recorrer los días en lugar de hacer query por día
                $lChecks = $lChecks->where('e.id', $idEmployee)
                                    ->where('r.date', $sDate)
                                    ->get();
                
                if (sizeof($lChecks) == 0) {
                    // checar incidencias ********************************************************
                    $lAbsences = $this->searchAbsence($idEmployee, $sDate);
                    if (sizeof($lAbsences) > 0) {
                        foreach ($lAbsences as $absence) {
                            $key = explode("_", $absence->external_key);

                            $abs = [];
                            $abs['id_emp'] = $key[0];
                            $abs['id_abs'] = $key[1];
                            $abs['nts'] = $absence->nts;

                            $day->absences[] = $abs;
                        }
                    }
                }
                else {
                    // verificar checadas **************************************************************
                    foreach ($lChecks as $check) {
                        if ($check->type_id == \SCons::REG_IN) {
                            $day->entry = $check->time;
                        } else {
                            $day->leave = $check->time;
                        }
                    }
                }

                // checar horarios *******************************************************************
                $lAssigns = SDelayReportUtils::hasAnAssing($idEmployee, 0, $startDate, $endDate);
                $registry = (object)[
                    'date' => $sDate,
                    'time' => '12:00:00'
                ];

                $bCheckWorkshifts = true;;
                if ($lAssigns != null) {
                    /**
                     * busca el horario correspondiente en base a la hora de entrada
                     */
                    $result = SDelayReportUtils::processRegistry($lAssigns, $registry, \SCons::REP_DELAY);

                    if ($result != null) {
                        $day->prog_entry = $result->auxScheduleDay->entry;
                        $day->prog_leave = $result->auxScheduleDay->departure;

                        $bCheckWorkshifts = false;
                    }
                }

                if ($bCheckWorkshifts) {
                    $lworks = clone $lWorkshifts;

                    /**
                     * busca el horario en base a las tablas de workshift
                     */
                    $result = SDelayReportUtils::checkSchedule($lworks, $idEmployee, $registry, null);
                    if ($result != null) {
                        $day->prog_entry = $result->entry;
                        $day->prog_leave = $result->departure;
                    }
                }

                $row->days[] = $day;
            }

            $prePayroll->rows[] = $row;
        }

        return $prePayroll;
    }

    /**
     * Determina si el empleado tiene incidencias para el día en custión,
     * regresa un arreglo con las incidencias correspondientes
     *
     * @param int $idEmployee
     * @param String $sDate
     * 
     * @return incident array
     */
    private function searchAbsence($idEmployee, $sDate)
    {
        $lAbsences = incident::where('employee_id', $idEmployee)
            ->whereRaw("'" . $sDate . "' BETWEEN start_date AND end_date")
            ->select('external_key', 'nts')
            ->orderBy('id', 'ASC')
            ->get();

        return $lAbsences;
    }
}
