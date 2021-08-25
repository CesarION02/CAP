<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\SUtils\SInfoWithPolicy;
use App\Models\employees;
use App\Models\incident;
use App\Models\cutCalendarQ;
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
     *              num_absences: int,
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
         * Obtiene el reporte de horas extra, que contiene también domingos y festivos.
         */
        $info = SInfoWithPolicy::preProcessInfo($startDate, $oStartDate->year, $endDate, $payType);
        $lExtras = \DB::table('processed_data')
                                ->join('employees','employees.id','=','processed_data.employee_id')
                                ->whereIn('employees.id', $lCapEmployees)
                                ->whereBetween('outDate',[$startDate, $endDate])
                                ->get();

        $prePayroll = new SPrePayroll();
        $prePayroll->start_date = $startDate;
        $prePayroll->end_date = $endDate;
        $prePayroll->rows = [];

        foreach ($lEmployees as $idEmployee => $extId) {
            $row = new SPrePayrollRow();
            $row->employee_id = $extId;
            $row->days = [];

            /**
             * Determinar horas extras dobles y triples
             */
            $lCExtrasEmp = clone collect($lExtras);
            $lCExtrasEmp = $lCExtrasEmp->where('employee_id', $idEmployee);
            $lGrouped = $lCExtrasEmp->groupBy('employee_id')->map(function ($row) {
                                                $registry = (object) [
                                                    'minsExtraDouble' => $row->sum('extraDobleMins'),
                                                    'minsExtraTriple' => $row->sum('extraTripleMins'),
                                                    'minsExtraDoubleNoOficial' => $row->sum('extraDobleMinsNoficial'),
                                                    'minsExtraTripleNoOficial' => $row->sum('extraTripleMinsNoficial'),
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
                        $row->double_overtime = $lGrouped{$idEmployee}->minsExtraDoubleNoOficial / 60;
                        $row->triple_overtime = $lGrouped{$idEmployee}->minsExtraTripleNoOficial / 60;
                        break;
    
                    case \SCons::ALL_DATA:
                        $row->double_overtime = ($lGrouped{$idEmployee}->minsExtraDouble + 
                                                    $lGrouped{$idEmployee}->minsExtraDoubleNoOficial) / 60;
                        $row->triple_overtime = ($lGrouped{$idEmployee}->minsExtraTriple +
                                                    $lGrouped{$idEmployee}->minsExtraTripleNoOficial) / 60;
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }

            foreach ($aDates as $sDate) {
                $lCExtrasDay = clone collect($lExtras);
                $lCExtrasDay = $lCExtrasDay->where('outDate', $sDate);
                $lCExtrasDay = $lCExtrasDay->where('employee_id', $idEmployee);
                
                $day = new SPrePayrollDay();
                $day->dt_date = $sDate;

                if ($dataType == \SCons::LIMITED_DATA || $dataType == \SCons::ALL_DATA) {
                    /**
                     * Determinar faltas en el periodo
                     */
                    $lColl = clone $lCExtrasDay;
                    $withAbs = $lColl->filter(function ($item) {
                                    return ($item->hasabsence);
                                });
                    $day->num_absences = sizeof($withAbs);
    
                    /**
                     * Determinar primas dominicales
                     */
                    $lColl = clone $lCExtrasDay;
                    $withSunday = $lColl->filter(function ($item) {
                                    return ($item->is_sunday > 0);
                                });
                    $day->is_sunday = sizeof($withSunday);
    
                    /**
                     * Obtención de incidencias o eventos
                     */
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
                    }
                }

                if ($dataType == \SCons::OTHER_DATA || $dataType == \SCons::ALL_DATA) {
                    
                    /**
                     * Obtener descansos
                     */
                    $lColl = clone $lCExtrasDay;
                    $lColl = $lColl->where('work_dayoff', true);
                    $withDaysOff = $lColl->groupBy('employee_id')->map(function ($row) {
                                                $registry = (object) [
                                                    'daysOff' => $row->sum('is_dayoff'),
                                                ];
    
                                                return $registry;
                                            });
                    if (sizeof($withDaysOff) > 0) {
                        $day->n_days_off = $withDaysOff{$idEmployee}->daysOff;
                    }

                     /**
                     * Obtener festivos
                     */
                    $lColl = clone $lCExtrasDay;
                    $lColl = $lColl->where('is_granted', false);
                    $withHolidays = $lColl->groupBy('employee_id')->map(function ($row) {
                                                $registry = (object) [
                                                    'holidays' => $row->sum('is_holiday'),
                                                ];
    
                                                return $registry;
                                            });
                    if (sizeof($withHolidays) > 0) {
                        $day->holiday_id = $withHolidays{$idEmployee}->holidays;
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
    public static function searchAbsence($idEmployee, $sDate)
    {
        $lAbsences = \DB::table('incidents AS i')
            ->join('type_incidents AS ti', 'i.type_incidents_id', '=', 'ti.id')
            ->where('employee_id', $idEmployee)
            ->whereRaw("'" . $sDate . "' BETWEEN start_date AND end_date")
            ->select('i.external_key', 'i.nts', 'ti.name AS type_name', 'i.id', 'ti.id AS type_id')
            ->where('i.is_delete', false)
            ->orderBy('i.id', 'ASC')
            ->get();

        return $lAbsences;
    }

    /**
     * Importación de fechas de corte de quincenas para prenómina
     */

    public function saveCutCalendarFromJSON($lSiieCutsJ)
    {
        $lCapCuts = cutCalendarQ::select('id', 'external_id')
                                    ->pluck('id', 'external_id');

        $lSiieCutsCol = collect($lSiieCutsJ);
        $lSiieCuts = $lSiieCutsCol->where('fk_tp_pay', 2); // filtrar para quincenas
        
        foreach ($lSiieCuts as $jCut) {
            try {
                if (isset($lCapCuts[$jCut->id_cal])) {
                    $id = $lCapCuts[$jCut->id_cal];
                    $this->updCutPrepayQ($jCut, $id);
                }
                else {
                    $this->insertCutPrepayQ($jCut);
                }
            }
            catch (\Throwable $th) { }
        }
    }
    
    private function updCutPrepayQ($jCut, $id)
    {
        cutCalendarQ::where('id', $id)
                    ->update(
                            [
                            'dt_cut' => $jCut->dt_cut,
                            'year' => $jCut->year,
                            'num' => $jCut->num,
                            'is_delete' => $jCut->is_deleted,
                            ]
                        );
    }
    
    private function insertCutPrepayQ($jCut)
    {
        $cut = new cutCalendarQ();

        $cut->year = $jCut->year;
        $cut->num = $jCut->num;
        $cut->dt_cut = $jCut->dt_cut;
        $cut->external_id = $jCut->id_cal;
        $cut->is_delete = $jCut->is_deleted;

        $cut->save();
    }
}
