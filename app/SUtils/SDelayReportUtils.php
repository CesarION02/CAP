<?php namespace App\SUtils;

use Carbon\Carbon;

class SDelayReportUtils {

    /**
     * var_dump(Carbon::SUNDAY);     // int(0)
     * var_dump(Carbon::MONDAY);     // int(1)
     * var_dump(Carbon::TUESDAY);    // int(2)
     * var_dump(Carbon::WEDNESDAY);  // int(3)
     * var_dump(Carbon::THURSDAY);   // int(4)
     * var_dump(Carbon::FRIDAY);     // int(5)
     * var_dump(Carbon::SATURDAY);   // int(6)
     */

    public static function processReport($sStartDate, $sEndDate, $payWay)
    {
        $registries = SDelayReportUtils::getRegistries($sStartDate, $sEndDate, $payWay);
        $lWorkshifts = SDelayReportUtils::getWorkshifts($sStartDate, $sEndDate, $payWay);

        $lRows = array();
        $idEmployee = 0;
        $idDepartment = 0;
        $isNew = true;
        $newRow = null;
        $count = 0;
        $rows = sizeof($registries);

        foreach ($registries as $registry) {
            if ($registry->employee_id != $idEmployee) {
                $idEmployee = $registry->employee_id;
                $idDepartment = $registry->dept_id;

                $lAssigns = SDelayReportUtils::hasAnAssing($idEmployee, $idDepartment, $sStartDate, $sEndDate);
            }

            $theRow = SDelayReportUtils::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, $lWorkshifts);
            $isNew = $theRow[0];
            $newRow = $theRow[1];
            $again = $theRow[2];

            if ($isNew) {
                $lRows[] = $newRow;
            }

            if ($again) {
                $theRow = SDelayReportUtils::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, $lWorkshifts);
                $isNew = $theRow[0];
                $newRow = $theRow[1];

                if ($isNew) {
                    $lRows[] = $newRow;
                }
            }

            if (! $isNew && $count == ($rows -1)) {
                $lRows[] = $newRow;
            }

            $count++;
        }

        return $lRows;
    }

    private static function manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, $qWorkshifts)
    {
        $lWorkshifts = clone $qWorkshifts;
        $hasAssign = $lAssigns != null;
        $again = false;

        if ($isNew) {
            $newRow = new SRegistryRow();
            $newRow->idEmployee = $idEmployee;
            $newRow->numEmployee = $registry->num_employee;
            $newRow->employee = $registry->name;
        }

        if ($registry->type_id == 1) {
            if ($hasAssign) {
                $result = SDelayReportUtils::processRegistry($lAssigns, $registry);
            }
            else {
                $result = SDelayReportUtils::checkSchedule($lWorkshifts, $idEmployee, $registry);
            }

            // no tiene horario para el día actual
            if ($result == null) {
                if ($isNew) {
                    $isNew = false;
                    $newRow->inDate = $registry->date;
                    $newRow->inDateTime = $registry->date.' '.$registry->time;
                    $newRow->comments = $newRow->comments."Sin horario".",";
                }
                else {
                    $isNew = true;
                    $again = true;
                    $newRow->comments = $newRow->comments."Falta salida".",";
                }
            }
            else {
                if ($newRow->inDate == null) {
                    if ($newRow->outDate == null) {
                        $newRow->inDate = $result->variableDateTime->toDateString();
                        $newRow->inDateTime = $result->variableDateTime->toDateTimeString();
                        $newRow->delayMins = $result->delayMins;
    
                        $isNew = false;
                    }
                }
                else {
                    //falta salida
                    $isNew = true;
                    $again = true;
                    $newRow->comments = $newRow->comments."Falta salida".",";
                }
            }

        }
        else {
            if ($newRow->outDate == null) {
                if ($newRow->inDate != null) {
                    $newRow->outDate = $registry->date;
                    $newRow->outDateTime = $registry->date.' '.$registry->time;

                    $isNew = true;
                }
                else {
                    // falta entrada
                    $newRow->outDate = $registry->date;
                    $newRow->outDateTime = $registry->date.' '.$registry->time;
                    $newRow->comments = $newRow->comments."Falta entrada".",";

                    $isNew = true;
                }
            }
        }

        $response = array();
        $response[] = $isNew;
        $response[] = $newRow;
        $response[] = $again;

        return $response;
    }

    public static function getRegistries($startDate, $endDate, $payWay)
    {
        // \DB::enableQueryLog();

         // se obtiene el conjunto de checadas correspondientes al periodo con 
        // los distintos datos correspondientes al empleado
        $registries = \DB::table('registers AS r')
                                ->join('employees AS e', 'e.id', '=', 'r.employee_id')
                                ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                                ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                                ->whereBetween('date', [$startDate, $endDate])
                                ->select('r.*', 'd.id AS dept_id', 'e.num_employee', 'e.name')
                                ->orderBy('employee_id', 'ASC')
                                ->orderBy('date', 'ASC')
                                ->orderBy('type_id', 'ASC');
                                // ->where('employee_id', '79');

        switch ($payWay) {
            case 1:
                $registries = $registries->where('e.way_pay_id', 1);
                break;
            case 2:
                $registries = $registries->where('e.way_pay_id', 2);
                break;
            
            default:
                # code...
                break;
        }

        $registries = $registries->get();

        // dd(\DB::getQueryLog());

        return $registries;
    }

    public static function getWorkshifts($startDate, $endDate, $payWay)
    {
        $lWorkshifts = \DB::table('week_department_day AS wdd')
                            ->join('day_workshifts AS dw', 'wdd.id', '=', 'dw.day_id')
                            ->join('day_workshifts_employee AS dwe', 'dw.id', '=', 'dwe.day_id')
                            ->join('workshifts AS w', 'dw.workshift_id', '=', 'w.id')
                            ->join('employees AS e', 'dwe.employee_id', '=', 'e.id')
                            ->select('wdd.date', 'w.name', 'w.entry', 'w.departure')
                            ->whereBetween('wdd.date', [$startDate, $endDate]);

        switch ($payWay) {
            case 1:
                $lWorkshifts = $lWorkshifts->where('e.way_pay_id', 1);
                break;
            case 2:
                $lWorkshifts = $lWorkshifts->where('e.way_pay_id', 2);
                break;
            
            default:
                # code...
                break;
        }

        return $lWorkshifts;
    }
    
    public static function hasAnAssing($idEmployee, $idDepartment, $startDate, $endDate)
    {
        // \DB::enableQueryLog();

        /**
         * se verifica si el empleado tiene asignaciones correspondientes
         * al periodo de consulta del reporte
         */
        $base = \DB::table('schedule_assign AS sa')
                    ->where('is_delete', false)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->where(function ($query) use ($startDate, $endDate) {
                            $query->where('start_date', '<=', $endDate)
                                    ->where(function ($query) use ($startDate) {
                                        $query->where('end_date', '>=', $startDate)
                                            ->orWhereNull('end_date');
                                    })
                                    ->orWhereNull('start_date');
                        });
                    })
                    ->orderBy('start_date', 'DESC')
                    ->orderBy('group_schedules_id')
                    ->orderBy('order_gs', 'ASC');

        $assings = clone $base;
                    
        $assings = $assings->where('employee_id', $idEmployee)
                            ->get();

        // si el empleado no tiene asignados horarios se consulta si hay
        // asignaciones por departamento
        if (! sizeof($assings) > 0 && $idDepartment > 0) {
            $assings = clone $base;

            $assings = $assings->where('department_id', $idDepartment)
                                ->get();
        }

        // dd(\DB::getQueryLog());

        if (! sizeof($assings) > 0) {
            return null;
        }

        return $assings;
    }

    public static function processRegistry($lAassigns, $registry)
    {
        /**
         * si la fecha de inicio de la asignación es nula, significa que dicha
         * asignación es indefinida y es el horario normal del empleado
         */
        if ($lAassigns[0]->start_date == null) {
            //si el grupo de horarios es nullo significa que solo tiene asignado un horario
            // por lo que la comparación se hace directa con el día
            if ($lAassigns[0]->group_schedules_id == null) {
                return SDelayReportUtils::compareTemplate($lAassigns[0]->schedule_template_id, $registry);
            }
            else {
                /**
                 * Si el grupo no es nulo, se consultan cuantos horarios tiene asignados el empleado
                 * para realizar el recorrido y verificar en qué horario se encuentra actualmente
                 */
                $grpSchId = $lAassigns[0]->group_schedules_id;

                $assignsTemplates = array();
                foreach ($lAassigns as $assign) {
                    if ($assign->group_schedules_id == $grpSchId) {
                        $assignsTemplates[] = $assign;
                        continue;
                    }

                    break;
                }

                $comparisons = array();
                //recorrido de los horarios que el empleado tiene asignados
                //cuando no tiene fecha de inicio y existen asigandos más de un template
                foreach ($assignsTemplates as $ass_template) {
                    $comparisons[] = SDelayReportUtils::compareTemplate($ass_template->schedule_template_id, $registry);
                }

                // ordenar las asignaciones en base a los minutos de retardo
                usort($comparisons, function($a, $b)
                {
                    return (abs($a->delayMins) - abs($b->delayMins));
                });

                return $comparisons[0];
            }
        }
        else {
            foreach ($lAassigns as $assign) {
                if ($assign->start_date <= $registry->date && // funciona la comparación?
                    (($assign->end_date != null &&  $assign->end_date >= $registry->date) ||
                    $assign->end_date == null)) { 
                        $result = SDelayReportUtils::compareTemplate($assign->schedule_template_id, $registry);

                        return $result;
                }
            }

            return null;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $templateId
     * @param [type] $registry
     * 
     * @return SDateComparison comparison object
     *   variableDateTime
     *   pinnedDateTime
     *   delayMins
     */
    public static function compareTemplate($templateId, $registry)
    {
        $oDate = Carbon::parse($registry->date.' '.$registry->time);
        // Carbon::setWeekStartsAt(Carbon::FRIDAY);
        $day = ($oDate->toObject()->dayOfWeek + 1); // los días en Carbon inician en 0, así que hay que sumar uno
        
        /**
         * Se consulta el horario que corresponde al día de la 
         * semana de la fecha de checada
         */
        $templateDay = \DB::table('schedule_day AS sd')
                            ->where('schedule_template_id', $templateId)
                            ->where('day_num', $day)
                            ->get();

        $oScheduleDay = $templateDay[0];

        return SDelayReportUtils::compareDates($registry->date.' '.$oScheduleDay->entry, $registry->date.' '.$registry->time);
    }

    /**
     * Undocumented function
     *
     * @param String $sDateOne puede ser considerada como la fecha de referencia o fija.
     * @param String $sDateTwo fecha variable (checada)
     * 
     * @return SDateComparison 
     * 
     */
    public static function compareDates($sDateOne, $sDateTwo)
    {
        $oDate1 = Carbon::parse($sDateOne);
        $oDate2 = Carbon::parse($sDateTwo);

        $comparison = new SDateComparison();
        $comparison->pinnedDateTime = $oDate1;
        $comparison->variableDateTime = $oDate2;

        $mins = $oDate1->diffInMinutes($oDate2);

        if ($oDate1->greaterThan($oDate2)) {
            $mins *= -1;
        }

        $comparison->delayMins = $mins;

        return $comparison;
    }

    public static function checkSchedule($lWorkshifts, $idEmployee, $registry)
    {
        $lWEmployee = $lWorkshifts->where('e.id', $idEmployee)
                                    ->where('wdd.date', $registry->date);
        $lWEmployee = $lWEmployee->get();

        if (sizeof($lWEmployee) == 0) {
            return null;
        }

        $workshift = $lWEmployee[0];
        
        return SDelayReportUtils::compareDates($workshift->date.' '.$workshift->entry, $registry->date.' '.$registry->time);
    }
}

?>
