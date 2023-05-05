<?php namespace App\SUtils;

use App\Http\Controllers\prePayrollController;
use App\SData\SDataProcess;
use App\SUtils\SDateTimeUtils;
use Carbon\Carbon;

class SReportsUtils {

    public static function setAbsencesAndHolidays(int $idEmployee, string $date, $oRow) {
        $lAbsences = prePayrollController::searchAbsence($idEmployee, $date);
                
        if (sizeof($lAbsences) > 0) {
            foreach ($lAbsences as $absence) {
                $key = explode("_", $absence->external_key);

                $abs = [];
                $abs['id_emp'] = $key[0];
                $abs['id_abs'] = $key[1];
                $abs['nts'] = $absence->nts;
                $abs['type_name'] = $absence->type_name;
                $oRow->others = (isset($oRow->others) ? $oRow->others : "")."".$absence->type_name.". ";

                $oRow->events[] = $abs;
            }
        }

        $holidays = SDataProcess::getHolidays($date);

        if ($holidays != null) {
            $num = sizeof($holidays);
    
            if ($num > 0) {
                $oRow->isHoliday = $num;
                $oRow->others = (isset($oRow->others) ? $oRow->others.'Festivo. ' : 'Festivo');
            }
        }

        return $oRow;
    }

    public static function getCssClass($oRow)
    {
        if ((isset($oRow->events) && sizeof($oRow->events) > 0) || (isset($oRow->isHoliday) && $oRow->isHoliday > 0)) {
            return "events";
        }
    }

    /**
     * Agrupa los renglones obtenidos de la función del reporte de tiempos extra y contabiliza cada uno
     * de los atributos que se tienen que mostrar.
     *
     * @param array $lRows
     * @param \Illuminate\Support\Collection $lEmployees
     * @param string $startDate
     * @param string $endDate
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function resumeReportRows($lRows, $lEmployees, $startDate, $endDate)
    {
        $iDaysInPeriod = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        foreach ($lEmployees as $lEmployee) {
            $delayTot = 0;
            $prematureOutTot = 0;
            $extraHoursTot = 0;
            $sundays = 0;
            $daysOff = 0;
            $absences = 0;
            $vacations = "";
            $absenceByEvent = "";
            $inability = "";
            $payableEvents = "";
            $noPayableEvents = "";
            $countVacations = 0;
            $countAbsenceByEvent = 0;
            $countInability = 0;
            // $countPayableEvents = 0;
            // $countNoPayableEvents = 0;
            $countDaysToPay = $iDaysInPeriod;

            $lEmpRows = clone collect($lRows);
            $lRowsOfEmployee = $lEmpRows->where('idEmployee', $lEmployee->id);

            foreach ($lRowsOfEmployee as $oRow) {
                $delayTot = $delayTot + $oRow->entryDelayMinutes;
                $mins = SDateTimeUtils::getExtraTimeByRules($oRow->overMinsTotal);
                $extraHoursTot += $mins;
                $prematureOutTot += $oRow->prematureOut;
                $sundays += $oRow->isSunday;
                $daysOff += $oRow->isDayOff;

                if ($oRow->hasAbsence) {
                    $absences++;
                    $countDaysToPay--;
                    $noPayableEvents .= (strlen($noPayableEvents) > 0 ? "-" : "") . "Falta";
                }

                foreach ($oRow->events as $aEvent) {
                    $oEvent = (object) $aEvent;
                    switch ($oEvent->type_id) {
                        case \SCons::INC_TYPE['INA_S_PER']: // INASIST. S/PERMISO
                            $absenceByEvent .= $oEvent->type_name.(!is_null($oEvent->nts) && !empty($oEvent->nts) ? ("-".$oEvent->nts) : "").", ";
                            $countAbsenceByEvent++;
                            $noPayableEvents .= $oEvent->type_name.(! is_null($oEvent->nts) && !empty($oEvent->nts) ? ("-".$oEvent->nts) : "").", ";
                            break;

                        case \SCons::INC_TYPE['INA_C_PER_SG']: // INASIST. C/PERMISO S/GOCE
                        case \SCons::INC_TYPE['INA_AD_REL_CH']: // INASIST. ADMTIVA. RELOJ CHECADOR
                        case \SCons::INC_TYPE['INA_AD_SUSP']: // INASIST. ADMTIVA. SUSPENSIÓN
                        case \SCons::INC_TYPE['INA_AD_OT']: // INASIST. ADMTIVA. OTROS
                            $noPayableEvents .= $oEvent->type_name.(! is_null($oEvent->nts) && !empty($oEvent->nts) ? ("-".$oEvent->nts) : "").", ";
                            // $countNoPayableEvents++;
                            break;

                        case \SCons::INC_TYPE['DESCANSO']: // DESCANSO
                            $daysOff++;
                            break;

                        case \SCons::INC_TYPE['INA_C_PER_CG']: // INASIST. C/PERMISO C/GOCE
                        case \SCons::INC_TYPE['RIESGO']: // Riesgo de trabajo
                        case \SCons::INC_TYPE['ENFERMEDAD']: // Enfermedad en general
                        case \SCons::INC_TYPE['MATER']: // Maternidad
                        case \SCons::INC_TYPE['LIC_CUIDADOS']: // Licencia por cuidados médicos de hijos diagnosticados con cáncer
                        case \SCons::INC_TYPE['CAPACIT']: // CAPACITACIÓN
                        case \SCons::INC_TYPE['TRAB_F_PL']: // TRABAJO FUERA PLANTA
                        case \SCons::INC_TYPE['PATER']: // PATERNIDAD
                        case \SCons::INC_TYPE['DIA_OTOR']: // DIA OTORGADO
                        case \SCons::INC_TYPE['INA_PRES_MED']: // INASIST PRESCRIPCION MEDICA
                        case \SCons::INC_TYPE['INA_TR_F_PL']: // INASIST TRABAJO FUERA DE PLANTA
                        case \SCons::INC_TYPE['ONOM_CAP']: // ONOMÁSTICO
                        case \SCons::INC_TYPE['ONOM_EXT']: // ONOMÁSTICO
                            $payableEvents .= $oEvent->type_name.(! is_null($oEvent->nts) && !empty($oEvent->nts) ? ("-".$oEvent->nts) : "").", ";
                            // $countPayableEvents++;
                            break;

                        case \SCons::INC_TYPE['INC_CAP']: // INCAPACIDAD
                            $inability .= $oEvent->type_name.(! is_null($oEvent->nts) && !empty($oEvent->nts) ? ("-".$oEvent->nts) : "").", ";
                            $countInability++;
                            $noPayableEvents .= $oEvent->type_name.(! is_null($oEvent->nts) && !empty($oEvent->nts) ? ("-".$oEvent->nts) : "").", ";
                            break;

                        case \SCons::INC_TYPE['VAC']: // VACACIONES
                        case \SCons::INC_TYPE['VAC_PEND']: // VACACIONES PENDIENTES
                        case \SCons::INC_TYPE['VAC_CAP']: // VACACIONES
                            $vacations .= $oEvent->type_name.(! is_null($oEvent->nts) && !empty($oEvent->nts) ? ("-".$oEvent->nts) : "").", ";
                            $countVacations++;
                            break;
                        
                        default:
                            # code...
                            break;
                    }

                    if (! $oEvent->is_payable) {
                        $countDaysToPay--;
                    }
                }
            }

            $lEmployee->entryDelayMinutes = $delayTot;
            $lEmployee->extraHours = $extraHoursTot;
            $lEmployee->prematureOut = $prematureOutTot;
            $lEmployee->isSunday = $sundays;
            $lEmployee->isDayOff = $daysOff;
            $lEmployee->hasAbsence = $absences;
            $lEmployee->vacations = $vacations;
            $lEmployee->absenceByEvent = $absenceByEvent;
            $lEmployee->inability = $inability;
            $lEmployee->payableEvents = $payableEvents;
            $lEmployee->noPayableEvents = $noPayableEvents;
            $lEmployee->countVacations = $countVacations;
            $lEmployee->countAbsenceByEvent = $countAbsenceByEvent;
            $lEmployee->countInability = $countInability;
            // $lEmployee->countPayableEvents = $countPayableEvents;
            // $lEmployee->countNoPayableEvents = $countNoPayableEvents;
            $lEmployee->countDaysToPay = $countDaysToPay;
        }

        return $lEmployees;
    }

    /**
     * Método de ordenamiento con varios parámetros
     *
     * @param \Illuminate\Support\Collection $collection
     * @param array $sorting_instructions [
     *                           ['column' => 'idEmployee', 'order' => 'asc'],
     *                           ['column' => 'inDateTime', 'order' => 'asc'],
     *                           ['column' => 'outDateTime', 'order' => 'asc'],
     *                       ]
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function multiPropertySort(\Illuminate\Support\Collection $collection, array $sorting_instructions) {
        return $collection->sort(function ($a, $b) use ($sorting_instructions) {
            foreach ($sorting_instructions as $sorting_instruction) {
                $a->{$sorting_instruction['column']} = (isset($a->{$sorting_instruction['column']})) ? $a->{$sorting_instruction['column']} : '';
                $b->{$sorting_instruction['column']} = (isset($b->{$sorting_instruction['column']})) ? $b->{$sorting_instruction['column']} : '';

                if (empty($sorting_instruction['order']) or strtolower($sorting_instruction['order']) == 'asc') {
                    $x = ($a->{$sorting_instruction['column']} <=> $b->{$sorting_instruction['column']});
                }
                else {
                    $x = ($b->{$sorting_instruction['column']} <=> $a->{$sorting_instruction['column']});

                }
                if ($x != 0) {
                    return $x;
                }
            }

            return 0;

        })->values();
    }

    /**
     * Filtrar empleados, solo aparecerán aquellos que hayan sido dados de alta antes de la fecha de inicio
     *
     * @param \Illuminate\Support\Collection $lEmployees
     * @param string $sStartDate en formato YYYY-mm-dd
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function filterEmployeesByAdmissionDate($lEmployees, $sStartDate, $key)
    {
        if (is_null($key)) {
            $aEmployees = $lEmployees->toArray();
        }
        else {
            $aEmployees = $lEmployees->pluck($key)->toArray();
        }

        if (count($aEmployees) == 0) {
            return collect([]);
        }

        $query = "SELECT
                    id
                FROM
                    employees
                WHERE
                    (admission_date >= leave_date
                    OR leave_date IS NULL)
                    AND admission_date < '".$sStartDate."'
                    AND id IN (".implode(",", $aEmployees).")";

        $empsByDates = \DB::select($query);

        $aEmpsByDates = collect($empsByDates)->pluck('id')->toArray();

        if (is_null($key)) {
            return collect($aEmpsByDates);
        }
        else {
            return $lEmployees->whereIn($key, $aEmpsByDates);
        }
    }

    /**
     * Determinar en la colección de empleados, si se tiene faltas y días de descanso trabajados
     *
     * @param \Illuminate\Database\Eloquent\Collection $lEmployees
     * @param \Illuminate\Database\Eloquent\Collection $lData
     * @param string $startDate
     * @param string $endDate
     * @param int $payWay
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function checkAbsencesAndDaysOff($lEmployees, $lData, $startDate, $endDate, $payWay)
    {
        if ($payWay == \SCons::PAY_W_Q) {
            $config = \App\SUtils\SConfiguration::getConfigurations();
            $cutDay = $config->startOfWeek;

            // Si el primer día de la semana no es lunes, busca hacia atrás el primer lunes
            $oStartDate = Carbon::parse($startDate);
            $oDate = Carbon::parse($startDate);
            if ($oStartDate->dayOfWeek != $cutDay) {
                $oDate->subDays($oStartDate->dayOfWeek - $cutDay);
            }
    
            // obtiene el rango del primer lunes al domingo
            $oFDate = Carbon::parse($oDate->format('Y-m-d'))->addDays(6);

            $lAdded = self::addWarningToCollection($lEmployees, $lData, $oDate->format('Y-m-d'), $oFDate->format('Y-m-d'), $payWay);

            // se hace el mismo proceso con la siguiente semana hasta que la fecha final de la semana esté fuera del rango de la quincena
            $oDate->addDays(7);
            $oFDate->addDays(7);
            while ($oFDate->lte($endDate)) {
                $lAdded = self::addWarningToCollection($lEmployees, $lAdded, $oDate->format('Y-m-d'), $oFDate->format('Y-m-d'), $payWay);
                $oDate->addDays(7);
                $oFDate->addDays(7);
            }

            return $lAdded;
        }
        else {
            return self::addWarningToCollection($lEmployees, $lData, $startDate, $endDate, $payWay);
        }
    }

    /**
     * Determinar en la colección de empleados, si se tiene faltas y días de descanso trabajados
     *
     * @param \Illuminate\Database\Eloquent\Collection $lEmployees
     * @param \Illuminate\Database\Eloquent\Collection $lData
     * @param string $weekStartDate
     * @param string $weekEndDate
     * @param int $payWay
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private static function addWarningToCollection($lEmployees, $lData, $weekStartDate, $weekEndDate, $payWay)
    {
        // obtiene el reporte de tiempos extra del periodo calculado y determina los días de descanso y las faltas
        $lRows = SDataProcess::process($weekStartDate, $weekEndDate, $payWay, $lEmployees);
        $aDatesDayOff = self::getDaysOffDates($lRows, $lEmployees);
        $lDatesDayOff = collect($aDatesDayOff);
        $filtered = $lDatesDayOff->filter(function ($item) {
                                                return !is_null($item->dateDayOffWorked);
                                            })->values();

        if (count($filtered) > 0) {
            foreach ($lDatesDayOff as $oDateOff) {
                if (is_null($oDateOff->dateDayOffWorked)) {
                    continue;
                }

                $oRow = $lData->where('idEmployee', $oDateOff->idEmployee)
                        ->where('outDate', $oDateOff->dateDayOffWorked)
                        ->filter(function ($item) {
                            if (count($item->events) > 0) {
                                foreach ($item->events as $oEvent) {
                                    if ($oEvent['type_id'] == \SCons::INC_TYPE['DESCANSO']) {
                                        return true;
                                    }
                                }
                            }
                        })->first();

                if (is_null($oRow)) {
                    continue;
                }
                
                $oRow->isDayChecked = true;
                $oRow->others = $oRow->others."Falta y descanso trabajado en la misma semana (detectado). ";
            }
        }

        return $lData;
    }

    /**
     * Obtener los días de descanso trabajados
     *
     * @param \Illuminate\Database\Eloquent\Collection $lData
     * @param \Illuminate\Database\Eloquent\Collection $lEmployees
     * 
     * @return array
     */
    private static function getDaysOffDates($lData, $lEmployees)
    {
        $lDatesDayOff = [];
        foreach ($lEmployees as $oEmp) {
            $lRangeRows = (clone $lData)->where('idEmployee', $oEmp->id);
            $hasAbsence = false;
            $hasDayOff = false;
            $hasDayOffWorked = false;
            $dateDayOffWorked = null;
            foreach ($lRangeRows as $oRow) {
                if ($oRow->hasAbsence) {
                    $hasAbsence = true;
                }
    
                if (count($oRow->events) > 0) {
                    foreach ($oRow->events as $oEvent) {
                        if ($oEvent['type_id'] == \SCons::INC_TYPE['DESCANSO']) {
                            $hasDayOff = true;
                            break;
                        }
                    }
                    if ($hasDayOff && $oRow->hasChecks) {
                        $hasDayOffWorked = true;
                        $dateDayOffWorked = $oRow->outDate;
                    }
                }
            }

            $oEmpObj = new \stdClass();
            $oEmpObj->idEmployee = $oEmp->id;
            // si no tiene faltas o no tiene descansos trabajados
            if ($hasAbsence && $hasDayOffWorked) {
                $oEmpObj->dateDayOffWorked = $dateDayOffWorked;
            }
            else {
                $oEmpObj->dateDayOffWorked = null;
            }

            $lDatesDayOff[] = $oEmpObj;
        }

        return $lDatesDayOff;
    }

    public static function checkManualCheck($lRows)
    {
        $lWithManualChecks = $lRows->filter(function ($item) {
            return $item->isModifiedIn || $item->isModifiedOut;
        })->values();

        if (count($lWithManualChecks) > 0) {
            foreach ($lWithManualChecks as $oRow) {
                $oRow->isDayChecked = true;
            }
        }

        return $lRows;
    }
}

