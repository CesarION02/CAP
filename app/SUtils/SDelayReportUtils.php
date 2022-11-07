<?php namespace App\SUtils;

use Carbon\Carbon;

class SDelayReportUtils {

    /**
     * Determina la hora de entrada programada en base al objeto recibido
     *
     * @param SDateComparison $oComparison
     * @return String "yyyy-MM-dd hh:mm:ss"
     */
    public static function getScheduleIn($oComparison, $inDateTime = null) {
        $night = false;
        $sDate = "";
        if ($oComparison->auxScheduleDay != null) {
            $oAux = $oComparison->auxScheduleDay;
            $night = $oAux->is_night;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                $oAux = $oComparison->auxWorkshift;
                if ($oAux->is_night) {
                    $night = true;
                }
            }
            else {
                return 0;
            }
        }

        $time = $oAux->entry;
        if ($night) {
            $oAuxDate = clone $oComparison->pinnedDateTime;
            $sDate = $oAuxDate->subDay()->toDateString();
        }
        else {
            if ($inDateTime != null) {
                $indtt = Carbon::parse($inDateTime);
                if ($indtt->toDateString() != $oComparison->pinnedDateTime->toDateString()) {
                    $sDate = $indtt->toDateString();
                }
                else {
                    $sDate = $oComparison->pinnedDateTime->toDateString();
                }
            }
            else {
                $sDate = $oComparison->pinnedDateTime->toDateString();
            }
        }

        return $sDate." ".$time;
    }

    /**
     * Determina la hora de entrada programada en base al objeto recibido
     *
     * @param SDateComparison $oComparison
     * @return String "yyyy-MM-dd hh:mm:ss"
     */
    public static function isNight($oComparison) {
        if ($oComparison->auxScheduleDay != null) {
            $oAux = $oComparison->auxScheduleDay;
            return $oAux->is_night;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                $oAux = $oComparison->auxWorkshift;
                return $oAux->is_night;
            }
        }

        return false;
    }

    /**
     * Devuelve el tiempo extra procedente de la base de datos,
     * correspondiente al tiempo que le corresponde por default
     *
     * @param SDateComparison $oComparison
     * @return int Minutos extra correspondientes
     */
    public static function getExtraTime($oComparison) {
        if ($oComparison->auxScheduleDay != null) {
            return $oComparison->auxScheduleDay->overtimepershift * 60;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                return $oComparison->auxWorkshift->overtimepershift * 60;
            }
            
            return 0;
        }
    }

    /**
     * Devuelve los minutos extra que le corresponden al empleado su tiene un
     * turno de más de 8 horas
     *
     * @param SDateComparison $oComparison
     * 
     * @return int Minutos extra correspondientes
     */
    public static function getExtraTimeByScheduleC($oComparison) {
        $mins = 0;
        $oAux = null;
        $night = false;
        if ($oComparison->auxScheduleDay != null) {
            $oAux = $oComparison->auxScheduleDay;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                $oAux = $oComparison->auxWorkshift;
                if ($oAux->name == "Noche") {
                    $night = true;
                }
            }
            else {
                return 0;
            }
        }
        
        $sDate = $oComparison->pinnedDateTime->toDateString();
        $date1 = $sDate.' '.$oAux->entry;
        if ($night) {
            $sDate = $oComparison->pinnedDateTime->addDay()->toDateString();
        }
        $date2 = $sDate.' '.$oAux->departure;
        $comp = SDelayReportUtils::compareDates($date1, $date2);
        
        $mins = abs($comp->diffMinutes);
        $scheduleTop = 8 * 60; // 8 horas
        
        if ($mins > $scheduleTop) {
            $extraMins = $mins - $scheduleTop;
            if ($extraMins > 240) {
                return 240;
            }

            return $extraMins;
        }
        
        return 0;
    }

    /**
     * Devuelve los minutos extra que le corresponden al empleado su tiene un
     * turno de más de 8 horas
     *
     * @param SDateComparison $oComparison
     * 
     * @return int Minutos extra correspondientes
     */
    public static function getExtraTimeBySchedule($oComparison, $inDateTime, $inDateTimeSch, $outDateTime, $outDateTimeSch) {
        if ($inDateTime == null || $inDateTimeSch == null || $outDateTime == null || $outDateTimeSch == null) {
            return 0;
        }

        $scheduleTop = 8 * 60; // 8 horas
        $compSch = SDelayReportUtils::compareDates($inDateTimeSch, $outDateTimeSch);

        if ($compSch->diffMinutes <= $scheduleTop) {
            return 0;
        }

        $mins = 0;
        $oAux = null;
        if ($oComparison->auxScheduleDay != null) {
            $oAux = $oComparison->auxScheduleDay;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                $oAux = $oComparison->auxWorkshift;
            }
            else {
                return 0;
            }
        }

        $maxOverMinsTime = $oAux->agreed_extra * 60;

        if ($maxOverMinsTime <= 0) {
            return 0;
        }

        $inComp = SDelayReportUtils::compareDates($inDateTime, $inDateTimeSch);

        $inDateComp = "";
        if ($inComp->diffMinutes < 0) {
            $inDateComp = $inDateTime;
        }
        else {
            $inDateComp = $inDateTimeSch;
        }

        $comp = SDelayReportUtils::compareDates($inDateComp, $outDateTime);
        
        $mins = abs($comp->diffMinutes);
        
        if ($mins > $scheduleTop) {
            $extraMins = $mins - $scheduleTop;
            if ($extraMins > $maxOverMinsTime) {
                return $maxOverMinsTime;
            }

            return $extraMins;
        }
        
        return 0;
    }
    
    /**
     * Obtiene la bandera de si se recorta entrada o salida
     *
     * @param SDateComparison $oComparison
     * @return int cut_id
     */
    public static function getCutId($oComparison) {
        if ($oComparison->auxScheduleDay != null) {
            return $oComparison->auxScheduleDay->cut_id;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                return $oComparison->auxWorkshift->cut_id;
            }
            
            return 0;
        }
    }


    /**
     * Obtiene la política de tiempo extra, para saber si este se le atribuye 
     *  a la fecha de entrada o salida
     *
     * @param SDateComparison $oComparison
     * @return int overtime_check_policy
     */
    public static function getOvertimePolicy($oComparison) {
        if ($oComparison->auxScheduleDay != null) {
            return $oComparison->auxScheduleDay->overtime_check_policy;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                return $oComparison->auxWorkshift->overtime_check_policy;
            }
            
            return 0;
        }
    }

    /**
     * Obtiene las checadas dado un rango de fechas y filtra por tipo de pago
     *
     * @param string $startDate [YYYY-MM-DD]
     * @param string $endDate [YYYY-MM-DD]
     * @param int $payWay [ 1: QUINCENA, 2: SEMANA, 0: TODOS]
     * @param array $lEmployees arreglo de ids de empleados
     * @param boolean $isArray si este parámetro es TRUE devuelve un arreglo, s es FALSE devuelve una query (sin get())
     * 
     * @return array ('r.*', 'd.id AS dept_id', 'e.num_employee', 'e.name')
     */
    public static function getRegistries($startDate, $endDate, $payWay, $lEmployees, $isArray)
    {
        // \DB::enableQueryLog();

         // se obtiene el conjunto de checadas correspondientes al periodo con 
        // los distintos datos correspondientes al empleado
        $registries = \DB::table('registers AS r')
                                ->join('employees AS e', 'e.id', '=', 'r.employee_id')
                                ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                                ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                                ->whereBetween('r.date', [$startDate, $endDate])
                                ->where('r.is_delete',0)
                                // ->select('r.*', 'd.id AS dept_id', 'e.num_employee', 'e.name', 'e.is_overtime')
                                ->select('r.*', 'd.id AS dept_id', 'e.num_employee', 'e.name', 'e.policy_extratime_id', 'e.external_id', 'd.area_id AS employee_area_id')
                                ->orderBy('employee_id', 'ASC')
                                ->orderBy('date', 'ASC')
                                ->orderBy('time', 'ASC');
                                // ->where('employee_id', '180');

        if (sizeof($lEmployees) > 0) {
            $registries = $registries->whereIn('e.id', $lEmployees);
        }

        switch ($payWay) {
            case \SCons::PAY_W_Q:
                $registries = $registries->where('e.way_pay_id', \SCons::PAY_W_Q);
                break;
            case \SCons::PAY_W_S:
                $registries = $registries->where('e.way_pay_id', \SCons::PAY_W_S);
                break;
            
            default:
                # code...
                break;
        }

        if ($isArray) {
            $registries = $registries->get();
        }

        // dd(\DB::getQueryLog());

        return $registries;
    }

    /**
     * Undocumented function
     *
     * @param [type] $sDate
     * @param [type] $iEmployee
     * @param int \SCons::REG_OUT \SCons::REG_IN
     * @return void
     */
    public static function getRegistry($sDate, $iEmployee, $iType, $time = "")
    {
        $registry = \DB::table('registers AS r')
                                ->join('employees AS e', 'e.id', '=', 'r.employee_id')
                                ->leftJoin('jobs AS j', 'j.id', '=', 'e.job_id')
                                ->leftJoin('departments AS d', 'd.id', '=', 'j.department_id')
                                ->where('r.date', $sDate)
                                ->where('e.id', $iEmployee)
                                // ->select('r.*', 'e.num_employee', 'e.name', 'e.is_overtime')
                                // ->select('r.*', 'e.num_employee', 'e.name', 'e.policy_extratime_id', 'e.external_id');
                                ->select('r.*', 'd.id AS dept_id', 'e.num_employee', 'e.name', 'e.policy_extratime_id', 'e.external_id', 'd.area_id AS employee_area_id');

        if ($iType == \SCons::REG_IN) {
            $registry = $registry->orderBy('date', 'DESC')
                                ->orderBy('time', 'DESC');
        }
        else {
            $registry = $registry->orderBy('date', 'ASC')
                                ->orderBy('time', 'ASC');
        }

        $registry = $registry->get();

        if ($time != "") {
            $config = \App\SUtils\SConfiguration::getConfigurations();

            foreach ($registry as $reg) {
                $oComparison = SDelayReportUtils::compareDates($reg->date.' '.$reg->time, $sDate.' '.$time);
                if (abs($oComparison->diffMinutes) <= $config->maxGapMinutes) {
                    return $reg;
                }
            }
        }

        foreach ($registry as $reg) {
            if ($iType == \SCons::REG_IN) {
                if ($reg->type_id == \SCons::REG_OUT) {
                    return null;
                }
                
                return $reg;
            }
            else {
                if ($reg->type_id == \SCons::REG_IN) {
                    return null;
                }

                return $reg;
            }
        }

        return null;
    }

    /**
     * Obtiene los horarios programados para los empleados que cumplan con el rango de fechas
     * y el tipo de pago
     *
     * @param string $startDate [YYYY-MM-DD]
     * @param string $endDate [YYYY-MM-DD]
     * @param int $payWay [ 1: QUINCENA, 2: SEMANA, 0: TODOS]
     * @param array $lEmployees arreglo de ids de empleados
     * 
     * @return query ('wdd.date', 'w.name', 'w.entry', 'w.departure')
     */
    public static function getWorkshifts($startDate, $endDate, $payWay, $lEmployees)
    {
        $lWorkshifts = \DB::table('week_department_day AS wdd')
                            ->join('day_workshifts AS dw', 'wdd.id', '=', 'dw.day_id')
                            ->join('day_workshifts_employee AS dwe', 'dw.id', '=', 'dwe.day_id')
                            ->join('workshifts AS w', 'dw.workshift_id', '=', 'w.id')
                            ->join('type_day AS td', 'dwe.type_day_id', '=', 'td.id')
                            ->join('employees AS e', 'dwe.employee_id', '=', 'e.id')
                            ->select('wdd.date', 
                                        'w.name', 
                                        'w.entry', 
                                        'w.overtimepershift',
                                        'w.departure', 
                                        'w.cut_id',
                                        'w.work_time',
                                        'w.overtime_check_policy',
                                        'w.is_night',
                                        'w.agreed_extra',
                                        'td.name AS td_name', 
                                        'td.short_name', 
                                        'dwe.type_day_id')
                            ->where('dwe.is_delete', false)
                            ->where('w.is_delete', false)
                            ->where('e.is_delete', false);
                            // ->whereBetween('wdd.date', [$startDate, $endDate]);

        if (sizeof($lEmployees) > 0) {
            $lWorkshifts = $lWorkshifts->whereIn('dwe.employee_id', $lEmployees);
        }

        switch ($payWay) {
            case \SCons::PAY_W_Q:
                $lWorkshifts = $lWorkshifts->where('e.way_pay_id', \SCons::PAY_W_Q);
                break;
            case \SCons::PAY_W_S:
                $lWorkshifts = $lWorkshifts->where('e.way_pay_id', \SCons::PAY_W_S);
                break;
            
            default:
                # code...
                break;
        }

        return $lWorkshifts;
    }
    
    /**
     * Consulta en la tabla de schedule_assign si el empleado tiene asignados horarios por empleado
     * y por departamento que cumplan con el rango de fechas recibido, que empiecen antes de la fecha inicial
     * y terminen después de esta, que estén ambas fechas dentro del rango, fechas indefinidas, o que empiecen antes 
     * o después de la fecha final y terminen dentro del rango o no terminen
     *
     * @param int $idEmployee
     * @param int $idDepartment departamento del empleado (busca por este medio solo si no encuentra referencias al empleado)
     * @param string $startDate [YYYY-MM-DD]
     * @param string $endDate [YYYY-MM-DD]
     * 
     * @return array  schedule_assign.*
     */
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
            $oDept = \DB::table('employees as e')
                        ->join('departments as d', 'd.id', '=', 'department_id')
                        ->where('e.id', $idEmployee)
                        ->where('d.is_delete', 0)
                        ->select('d.id as department_id', 'd.area_id as area_id')
                        ->first();

            if(!is_null($oDept)){
                $assings = clone $base;
    
                $assings = $assings->where('department_id', $oDept->department_id)
                                    ->get();
    
                if(! sizeof($assings) > 0){
                    $assings = clone $base;
    
                    $assings = $assings->where('area_id', $oDept->area_id)
                                        ->get();
                }
            }
        }

        // dd(\DB::getQueryLog());

        if (! sizeof($assings) > 0) {
            return null;
        }

        return $assings;
    }

    /**
     * Determina cuál es el horario que correspone al registro de checada y lo compara contra la hora
     * regresa null cuando no hay un horario que corresponda al registro
     *
     * @param array $lAassigns
     * @param query_registry $registry
     * @param int $tReport [\SCons::REP_DELAY, \SCons::REP_HR_EX]
     * 
     * @return SDateComparison object
     */
    public static function processRegistry($lAassigns, $registry, $tReport)
    {
        /**
         * si la fecha de inicio de la asignación es nula, significa que dicha
         * asignación es indefinida y es el horario normal del empleado
         */
        if ($lAassigns[0]->start_date == null) {
            //si el grupo de horarios es nullo significa que solo tiene asignado un horario
            // por lo que la comparación se hace directa con el día
            if ($lAassigns[0]->group_schedules_id == null) {
                return SDelayReportUtils::compareTemplate($lAassigns[0]->schedule_template_id, $registry, $tReport);
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
                    $comparisons[] = SDelayReportUtils::compareTemplate($ass_template->schedule_template_id, $registry, $tReport);
                }

                // ordenar las asignaciones en base al tiempo de retardo
                usort($comparisons, function($a, $b)
                {
                    return (abs($a->diffMinutes) - abs($b->diffMinutes));
                });

                return $comparisons[0];
            }
        }
        else {
            foreach ($lAassigns as $assign) {
                if ($assign->start_date <= $registry->date && // funciona la comparación?
                    (($assign->end_date != null &&  $assign->end_date >= $registry->date) ||
                    $assign->end_date == null)) {
                        if ($assign->group_schedules_id == null) {
                            return SDelayReportUtils::compareTemplate($assign->schedule_template_id, $registry, $tReport);
                        }
                        else if (isset($assign->employee_id) && $assign->employee_id > 0) {
                            $res = SDelayReportUtils::getScheduleAssignGrouped($assign->group_schedules_id, $registry->date, $assign->employee_id);
                            if ($res == null) {
                                continue;
                            }
                            else {
                                return SDelayReportUtils::compareTemplate($res, $registry, $tReport);
                            }
                        }
                }
            }

            return null;
        }
    }

    private static function getScheduleAssignGrouped($group, $dateToCompare, $employeeId)
    {
        $oDtCompare = Carbon::parse($dateToCompare);
        $schedules = \DB::table('schedule_assign AS sa')
                            ->where('sa.group_schedules_id', $group)
                            ->where('sa.is_delete', false)
                            ->where('sa.employee_id', $employeeId)
                            ->orderBy('sa.order_gs', 'ASC')
                            ->get();
        
        $count = sizeof($schedules);
        $firstDate = Carbon::parse($schedules[0]->start_date);
        $secondDate = (clone $firstDate)->addDays(6);

        $search = true;
        $i = 0;
        while ($search) {
                if ($oDtCompare->between($firstDate, $secondDate)) {
                    return $schedules[$i]->schedule_template_id;
                }
                $firstDate = (clone $firstDate)->addDays(7);
                $secondDate = (clone $firstDate)->addDays(6);
                $i++;
                if($i == $count){
                    $i = 0;
                }
                
        }
    }

    /**
     * Compara el registro recibido contra el template asociado al id recibido
     *
     * @param int $templateId
     * @param query_registry $registry
     * @param int $tReport [\SCons::REP_DELAY, \SCons::REP_HR_EX]
     * 
     * @return SDateComparison object
     */
    public static function compareTemplate($templateId, $registry, $tReport)
    {
        $oDate = Carbon::parse($registry->date.' '.$registry->time);
        // Carbon::setWeekStartsAt(Carbon::FRIDAY);
        $day = ($oDate->toObject()->dayOfWeek == 0 ? 7 : $oDate->toObject()->dayOfWeek); // los días en Carbon inician en 0, así que hay que sumar uno
        
        /**
         * Se consulta el horario que corresponde al día de la 
         * semana de la fecha de checada
         */
        $templateDay = \DB::table('schedule_day AS sd')
                            ->join('schedule_template AS st', 'sd.schedule_template_id', '=', 'st.id')
                            ->select('sd.id',
                                    'sd.day_name',
                                    'sd.day_num',
                                    'sd.entry',
                                    'sd.departure',
                                    'sd.is_active',
                                    'sd.schedule_template_id',
                                    'st.cut_id',
                                    'st.overtime_check_policy',
                                    'st.agreed_extra',
                                    'st.is_night',
                                    'st.name AS template_name',
                                    'st.overtimepershift')
                            ->where('schedule_template_id', $templateId)
                            ->where('day_num', $day)
                            // ->where('is_active', true)
                            ->get();
        
        if ($templateDay == null || sizeof($templateDay) == 0) {
            return null;
        }

        $oScheduleDay = $templateDay[0];

        $scheduleDate = $registry->date.' '.($tReport == \SCons::REP_DELAY ? $oScheduleDay->entry : $oScheduleDay->departure);

        $comparison = SDelayReportUtils::compareDates($scheduleDate, $registry->date.' '.$registry->time);
        $comparison->auxScheduleDay = $oScheduleDay;
        return $comparison;
    }

    /**
     * Compara las fechas recibidas y retorna el número de minutos de diferencia entre ellas,
     * cuando $sDateOne > $sDateTwo el valor retornado es negativo
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

        $comparison->diffMinutes = $mins;

        return $comparison;
    }

    /**
     * Convierte los minutos en entero a formato 00:00
     *
     * @param int $time
     * @param string $format
     * 
     * @return string 00:00
     */
    public static function convertToHoursMins($time, $format = '%02d:%02d') 
    {
        if ($time < 1) {
            return "00:00";
        }

        $hours = floor($time / 60);
        $minutes = ($time % 60);

        return sprintf($format, $hours, $minutes);
    }

    /**
     * filtra el empleado y la fecha del registro de la query de horarios para determinar
     * si dicho empleado tiene un horario asignado, retorna null si no hay horario asignado.
     *
     * @param query $lWorkshifts
     * @param int $idEmployee
     * @param query_registry $registry
     * @param int $mType [\SCons::REP_DELAY, \SCons::REP_HR_EX, null]
     *              Si el parámetro es \SCons::REP_DELAY compara contra fecha de entrada, si no contra fecha de salida
     * 
     * @return SDateComparison 
     */
    public static function checkSchedule($lWorkshifts, $idEmployee, $registry, $mType, $isSpecial = false, $specialApproved = true)
    {
        $lWEmployee = $lWorkshifts->where('e.id', $idEmployee)
                                    ->where('wdd.date', $registry->date)
                                    ->orderBy('wdd.created_at', 'DESC');

        if ($isSpecial) {
            $lWEmployee = $lWEmployee->whereNull('wdd.week_department_id');

            if ($specialApproved) {
                $lWEmployee = $lWEmployee->where('is_approved', $specialApproved);
            }
        }
                                    
        $lWEmployee = $lWEmployee->get();
        
        if (sizeof($lWEmployee) == 0) {
            return null;
        }
        
        if ($mType == null) {
            return $lWEmployee;
        }

        $workshift = $lWEmployee[0];

        $newDate = null;
        if ($workshift->is_night) {
            $newDate = Carbon::parse($registry->date)->subDay()->toDateString();
            $workshiftDate = $newDate.' '.($mType == \SCons::REP_DELAY ? $workshift->entry : $workshift->departure);
            $comparison = SDelayReportUtils::compareDates($workshiftDate, $newDate.' '.$registry->time);
        }
        else {
            $workshiftDate = $registry->date.' '.($mType == \SCons::REP_DELAY ? $workshift->entry : $workshift->departure);
            $comparison = SDelayReportUtils::compareDates($workshiftDate, $registry->date.' '.$registry->time);
        }

        $comparison->auxWorkshift = $workshift;

        $comparison->auxIsSpecialSchedule = $isSpecial;
        
        return $comparison;
    }

    public static function checkEvents($lWorkshifts, $idEmployee, $date, $eventId = 0)
    {
        $lWEmployee = $lWorkshifts->where('e.id', $idEmployee)
                                    ->where('wdd.date', $date);
        if ($eventId == 0) {
            $lWEmployee = $lWEmployee->where('dwe.type_day_id', '>', 1);
        }
        else {
            $lWEmployee = $lWEmployee->where('dwe.type_day_id', $eventId);
        }
                                    
        $lWEmployee = $lWEmployee->orderBy('wdd.created_at', 'DESC');

        $lWEmployee = $lWEmployee->get();

        if (sizeof($lWEmployee) == 0) {
            return null;
        }

        //$workshift = $lWEmployee[0];
        $workshift = $lWEmployee;

        return $workshift;
    }

    /**
     * Busca un horario asignado al empleado para la fecha seleccionada, tanto en assigns como en 
     * workshifts
     *
     * @param String $startDate
     * @param String $endDate
     * @param int $idEmployee
     * @param object $registry
     * @param collection $lWorkshifts
     * @param int $iRep [\SCons::REP_DELAY, \SCons::REP_HR_EX]
     * @param boolean $specialApproved
     * @return void
     */
    public static function getSchedule($startDate, $endDate, $idEmployee, $registry, $lWorkshifts, $iRep, $specialApproved = true) {
        // checar horario especial *******************************************************************
        $isSpecialWorkshift = true;
        $result = SDelayReportUtils::checkSchedule(clone $lWorkshifts, $idEmployee, $registry, $iRep, $isSpecialWorkshift, $specialApproved);

        if ($result != null) {
            $result->registry = $registry;

            return $result;
        }

        // checar horarios *******************************************************************
        $lAssigns = SDelayReportUtils::hasAnAssing($idEmployee, 0, $startDate, $endDate);

        if ($lAssigns != null) {
            $result = SDelayReportUtils::processRegistry($lAssigns, $registry, $iRep);

            if ($result != null) {
                // if ($result->auxScheduleDay->is_active) {
                    // $day->prog_entry = $result->auxScheduleDay->entry;
                    // $day->prog_leave = $result->auxScheduleDay->departure;
                    
                    //  $day->is_absence = true;
                    $result->registry = $registry;

                    return $result;
                // }
            }
        }

        /**
         * busca el horario en base a las tablas de workshift
        */
        $isSpecialWorkshift = false;
        $result = SDelayReportUtils::checkSchedule($lWorkshifts, $idEmployee, $registry, $iRep, $isSpecialWorkshift);

        if ($result != null) {
            $result->registry = $registry;
        } else {
            $lAssigns = SDelayReportUtils::hasAnAssing($idEmployee, 1, $startDate, $endDate);
            if ($lAssigns != null) {
                $result = SDelayReportUtils::processRegistry($lAssigns, $registry, $iRep);
    
                if ($result != null) {
                    $result->registry = $registry;
                    return $result;
                }
            }
        }
        
        return $result;
    }

    /**
     * Busca un horario a partir del día después del día de corte de semana
     *
     * @param String $date
     * @param String $time
     * @param int $idEmployee
     * @param query $lWorkshifts
     * @return void
     */
    public static function getNearSchedule($date, $time, $idEmployee, $lWorkshifts)
    {
        $oDate = Carbon::parse($date);
        $iDay = SDateTimeUtils::dayOfWeek($oDate);
        if ($iDay == \SCons::WEEK_START_DAY) {
            $oDate->subDays(1);
        }

        while (SDateTimeUtils::dayOfWeek($oDate) != \SCons::WEEK_START_DAY) {
            $registry = (object) [
                'date' => $oDate->toDateString(),
                'time' => $time
            ];
            
            $result = SDelayReportUtils::getSchedule($date, $date, $idEmployee, $registry, clone $lWorkshifts, \SCons::REP_HR_EX);
            
            if ($result == null || ($result->auxScheduleDay != null && !$result->auxScheduleDay->is_active)) {
                $oDate->subDays(1);
            }
            else {
                $result->oAuxDate = Carbon::parse($date.' '.$time);
                return $result;
            }
        }

        return null;
    }

    /**
     * Regresa verdadero si el día a analizar es domingo
     *
     * @param SRegistryRow $oRow
     * 
     * @return boolean
     */
    private static function isSunday($oRow)
    {
        // if ($oRow->inDate != null) {
        //     if (SDateTimeUtils::dayOfWeek($oRow->inDate) == Carbon::SUNDAY) {
        //         return true;
        //     }
        //     else {
        //         if ($oRow->inDate == $oRow->outDate) {
        //             return false;
        //         }
        //     }
        // }

        if ($oRow->outDate != null) {
            return SDateTimeUtils::dayOfWeek($oRow->outDate) == Carbon::SUNDAY;
        }
        
        return false;
    }

    public static function getTheoreticalDaysOffBasedOnDaysWorked($lData, $aEmployees, $startDate, $endDate)
    {
        $oStart = Carbon::parse($startDate);
        $oEnd = Carbon::parse($endDate);

        $diff = $oStart->diffInDays($oEnd);
        $diff++;

        if ($diff < 7) {
            return [];
        }

        $aDates = [];
        $oDate = clone $oStart;

        /**
         * crea un arreglo con los días a consultar
         */
        while ($oDate->lessThanOrEqualTo($oEnd)) {
            $aDates[] = $oDate->toDateString();
            $oDate->addDay();
        }

        $empWorkedDays = [];
        $lColData = collect($lData);
        foreach ($aEmployees as $idEmployee => $numEmployee) {
            $daysWorked = 0;
            foreach ($aDates as $sDate) {
                $data = clone $lColData;
                $info = $data->where('idEmployee', $idEmployee)
                            ->where('hasChecks', true);

                $result = $info->filter(function ($item) use ($sDate) {
                            $oIndate = Carbon::parse($item->inDateTime);
                            $oOutdate = Carbon::parse($item->outDateTime);
                                return ($oIndate->toDateString() == $sDate) || 
                                        ($oOutdate->toDateString() == $sDate);
                            });

                if (count($result) > 0) {
                    $daysWorked++;
                }
            }

            if ($daysWorked >= 7) {
                $empWorkedDays[$numEmployee] = intval($daysWorked / 7);
            }
            else {
                $empWorkedDays[$numEmployee] = 0;
            }
        }

        return $empWorkedDays;
    }
}

?>
