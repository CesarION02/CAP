<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SDateTimeUtils;
use App\SUtils\SInfoWithPolicy;
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
     *          double_overtime: decimal,
     *          triple_overtime: decimal,
     *			days: [
     *				{
     *				dt_date: date,
     *              is_absence: boolean,
     *              is_sunday: boolean,
     *              is_day_off: boolean,
     *              events: []
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
            'employees' => 'required',
            'pay_type' => 'required',
            'data_type' => 'required'
        ]);

        if (! $validator->passes()) {
            // return response()->json(['success'=>'Added new records.']);
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $aEmployeeIds = $request->employees;
        $payType = $request->pay_type == "1" ? \SCons::PAY_W_S : \SCons::PAY_W_Q;
        $dataType = $request->data_type;

        if (is_string($aEmployeeIds)) {
            $aEmployeeIds = explode(",", $aEmployeeIds);
        }

        $oPrepayroll = $this->makePrepayroll($startDate, $endDate, $aEmployeeIds, $payType, $dataType);

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
    private function makePrepayroll($startDate, $endDate, $aEmployeeIds, $payType, $dataType)
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

        /**
         * Obtiene el reporte de horas extra, que contiene también domingos y festivos.
         */
        $lExtras = SInfoWithPolicy::processInfo($startDate, $endDate, $payType, $lCapEmployees, $dataType);

        $prePayroll = new SPrePayroll();
        $prePayroll->start_date = $startDate;
        $prePayroll->end_date = $endDate;
        $prePayroll->rows = [];

        foreach ($lEmployees as $idEmployee => $extId) {
            $row = new SPrePayrollRow();
            $row->employee_id = $extId;
            $row->days = [];

            $lCExtrasEmp = clone collect($lExtras);
            $lGrouped = $lCExtrasEmp->groupBy('idEmployee')->map(function ($row) {
                                                $registry = (object) [
                                                    'minsExtraDouble' => $row->sum('extraDoubleMins'),
                                                    'minsExtraTriple' => $row->sum('extraTripleMins'),
                                                ];
        
                                        return $registry;
                                    });
                                    
            if (sizeof($lGrouped) > 0) {
                switch ($dataType) {
                    case \SCons::LIMITED_DATA:
                        $row->double_overtime = $lGrouped{$idEmployee}->minsExtraDouble / 60;
                        $row->triple_overtime = $lGrouped{$idEmployee}->minsExtraTriple / 60;
                        break;
                    case \SCons::OTHER_DATA:
                        $row->double_overtime = $lGrouped{$idEmployee}->minsExtraDouble / 60;
                        $row->triple_overtime = $lGrouped{$idEmployee}->minsExtraTriple / 60;
                        break;
    
                    case \SCons::ALL_DATA:
                        # code...
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }

            $lCExtrasEmp = clone collect($lExtras);

            foreach ($aDates as $sDate) {
                $day = new SPrePayrollDay();
                $day->dt_date = $sDate;

                $registry = (object) [
                    'date' => $sDate,
                    'time' => '12:00:00'
                ];

                $lChecks = clone $qChecks; // podría mejorarse a recorrer los días en lugar de hacer query por día
                $lChecks = $lChecks->where('e.id', $idEmployee)
                                    ->where('r.date', $sDate)
                                    ->get();
                
                // si no tiene checadas:                                    
                if (sizeof($lChecks) == 0) {
                    // checar incidencias ********************************************************
                    $lAbsences = prePayrollController::searchAbsence($idEmployee, $sDate);
                    
                    if (sizeof($lAbsences) > 0) {
                        foreach ($lAbsences as $absence) {
                            $key = explode("_", $absence->external_key);

                            $abs = [];
                            $abs['id_emp'] = $key[0];
                            $abs['id_abs'] = $key[1];
                            $abs['nts'] = $absence->nts;

                            $day->events[] = $abs;
                        }

                        continue;
                    }
                    else {
                        /**
                         * Si no tiene checadas y no tiene incidencias se revisa que no sea un día inactivo para el empleado
                         * Si es un día activo se le pone falta
                         */
                        $result = SDelayReportUtils::getSchedule($startDate, $endDate, $idEmployee, $registry, clone $lWorkshifts, \SCons::REP_DELAY);

                        if ($result != null) {
                            $day->is_absence = true;
                        }
                    }
                }
                else {
                    // verificar checadas **************************************************************
                    // foreach ($lChecks as $check) {
                    //     if ($check->type_id == \SCons::REG_IN) {
                    //         $day->entry[] = $check->time;
                    //     }
                    //     else {
                    //         $day->leave[] = $check->time;
                    //     }
                    // }
                    
                    //si tiene checada y es domingo se agrega domingo trabajado (PRIMA DOMINICAL)
                    $day->is_sunday = SDateTimeUtils::dayOfWeek($sDate) == Carbon::SUNDAY;
                }

                // Verifica en base al reporte de horas extra si el día corresponde a un día de descanso trabajado
                $nDaysOff = $lCExtrasEmp->where('idEmployee', $idEmployee)
                                            ->where('outDate', $sDate)
                                            ->sum('isDayOff');

                $day->n_days_off = $nDaysOff;

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
    public static function searchAbsence($idEmployee, $sDate)
    {
        $lAbsences = incident::where('employee_id', $idEmployee)
            ->whereRaw("'" . $sDate . "' BETWEEN start_date AND end_date")
            ->select('external_key', 'nts')
            ->orderBy('id', 'ASC')
            ->get();

        return $lAbsences;
    }
}
