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

     /**
      * Realiza el proceso de empatar checadas vs horarios programados y regresa una
      * lista de SRegistryRow con los datos correspondientes
      *
      * @param string $sStartDate
      * @param string $sEndDate
      * @param int $payWay [ 1: QUINCENA, 2: SEMANA, 0: TODOS]
      * @param int $tReport [\SCons::REP_DELAY, \SCons::REP_HR_EX]
      * @param array $lEmployees arreglo de ids de empleados

      * @return [SRegistryRow] (array)
      */
    public static function processReport($sStartDate, $sEndDate, $payWay, $tReport, $lEmployees)
    {
        $registries = SDelayReportUtils::getRegistries($sStartDate, $sEndDate, $payWay, $lEmployees, true);
        $lWorkshifts = SDelayReportUtils::getWorkshifts($sStartDate, $sEndDate, $payWay, $lEmployees);

        $lRows = array();
        $idEmployee = 0;
        $idDepartment = 0;
        $isNew = true;
        $newRow = null;
        $count = 0;
        $rows = sizeof($registries);

        foreach ($registries as $registry) {
            if ($registry->employee_id != $idEmployee) {
                if (! $isNew) {
                    $lRows[] = $newRow;
                    $isNew = true;
                    $newRow = null;
                }

                $idEmployee = $registry->employee_id;
                $idDepartment = $registry->dept_id;

                $lAssigns = SDelayReportUtils::hasAnAssing($idEmployee, $idDepartment, $sStartDate, $sEndDate);
            }

            if ($tReport == \SCons::REP_DELAY) {
                $theRow = SDelayReportUtils::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, $lWorkshifts);
            }
            else {
                $theRow = SDelayReportUtils::manageRowHrExt($newRow, $isNew, $idEmployee, $registry, $lAssigns, $lWorkshifts, $sStartDate, $sEndDate);
            }
            $isNew = $theRow[0];
            $newRow = $theRow[1];
            $again = $theRow[2];
            $fRegistry = $theRow[3];

            if ($isNew) {
                $lRows[] = $newRow;
            }

            if ($again) {
                if ($tReport == \SCons::REP_DELAY) {
                    $theRow = SDelayReportUtils::manageRow($newRow, $isNew, $idEmployee, $registry, $lAssigns, $lWorkshifts);
                }
                else {
                    if ($fRegistry != null) {
                        $theRow = SDelayReportUtils::manageRowHrExt($newRow, $isNew, $idEmployee, $fRegistry, $lAssigns, $lWorkshifts, $sStartDate, $sEndDate);
                    }
                    else {
                        $theRow = SDelayReportUtils::manageRowHrExt($newRow, $isNew, $idEmployee, $registry, $lAssigns, $lWorkshifts, $sStartDate, $sEndDate);
                    }
                }
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

    /**
     * Procesa el renglón de checada y busca si tiene un horario asignado, esta función es usada para 
     * el reporte de retardos, ya que consulta sobre el registro de entrada
     *
     * @param SRegistryRow $newRow
     * @param boolean $isNew
     * @param int $idEmployee
     * @param query_result $registry
     * @param query_assigns $lAssigns
     * 
     * @return array $response[0] = boolean que determina si el renglón está listo para ser agregado
     *               $response[1] = SRegistryRow que puede ser procesado de nuevo o estar completo
     *               $response[2] = boolean que determina si el renglón será reprocesado, esto cuando falta un registro de entrada o salida
     */
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

        if ($registry->type_id == \SCons::REG_IN) {
            if ($hasAssign) {
                $result = SDelayReportUtils::processRegistry($lAssigns, $registry, \SCons::REP_DELAY);
            }
            else {
                $result = SDelayReportUtils::checkSchedule($lWorkshifts, $idEmployee, $registry, \SCons::REP_DELAY);
            }

            // no tiene horario para el día actual
            if ($result == null) {
                if ($isNew) {
                    $isNew = false;
                    $newRow->inDate = $registry->date;
                    $newRow->inDateTime = $registry->date.'   '.$registry->time;
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
                        $newRow->inDateTime = $result->variableDateTime->format('Y-m-d   H:i:s');
                        // $newRow->inDateTime = $result->variableDateTime->toDateTimeString();
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
                    $newRow->outDateTime = $registry->date.'   '.$registry->time;

                    $isNew = true;
                }
                else {
                    // falta entrada
                    $newRow->outDate = $registry->date;
                    $newRow->outDateTime = $registry->date.'   '.$registry->time;
                    $newRow->comments = $newRow->comments."Falta entrada".",";

                    $isNew = true;
                }
            }
        }

        $response = array();
        $response[] = $isNew;
        $response[] = $newRow;
        $response[] = $again;
        $response[] = null;

        return $response;
    }

    /**
     * Procesa el renglón de checada y busca si tiene un horario asignado, esta función es usada para 
     * el reporte de horas extras, ya que consulta sobre el registro de salida
     *
     * @param SRegistryRow $newRow
     * @param boolean $isNew
     * @param int $idEmployee
     * @param query_result $registry
     * @param query_assigns $lAssigns
     * @param query $qWorkshifts
     * 
     * @return array $response[0] = boolean que determina si el renglón está listo para ser agregado
     *               $response[1] = SRegistryRow que puede ser procesado de nuevo o estar completo
     *               $response[2] = boolean que determina si el renglón será reprocesado, esto cuando falta un registro de entrada o salida
     */
    public static function manageRowHrExt($newRow, $isNew, $idEmployee, $registry, $lAssigns, $qWorkshifts, $sStartDate, $sEndDate)
    {
        $hasAssign = $lAssigns != null;
        $again = false;
        $oFoundRegistry = null;

        if ($isNew) {
            $newRow = new SRegistryRow();
            $newRow->idEmployee = $idEmployee;
            $newRow->numEmployee = $registry->num_employee;
            $newRow->employee = $registry->name;
        }

        if ($registry->type_id == \SCons::REG_OUT) {
            if ($hasAssign) {
                $result = SDelayReportUtils::processRegistry($lAssigns, $registry, \SCons::REP_HR_EX);
            }
            else {
                $result = SDelayReportUtils::checkSchedule(clone $qWorkshifts, $idEmployee, $registry, \SCons::REP_HR_EX);
            }

            // no tiene horario para el día actual
            if ($result == null) {
                if ($isNew) {
                    $isNew = false;
                    $again = true;
                    $newRow->comments = $newRow->comments."Falta entrada,";
                }
                else {
                    $otherResult = SDelayReportUtils::getNearSchedule($registry->date, $registry->time, $idEmployee, clone $qWorkshifts);
                    $isNew = true;

                    if ($otherResult != null) {
                        $newRow = SDelayReportUtils::setMinsToRow($newRow, $registry, $otherResult);

                        $newRow->isDayOff = 1;
                        // $newRow->others = $newRow->others."DESCANSO,";
                    }
                    else {
                        $newRow->outDate = $registry->date;
                        $newRow->outDateTime = $registry->date.'   '.$registry->time;
                        $newRow->comments = $newRow->comments."Sin horario".",";
                    }
                }
            }
            else {
                if ($newRow->inDate != null) {
                    if ($newRow->outDate == null) {
                        $newRow = SDelayReportUtils::setMinsToRow($newRow, $registry, $result);
    
                        $isNew = true;
                    }
                }
                else {
                    //falta entrada
                    $bFound = false;
                    if ($result->pinnedDateTime->toDateString() == $sStartDate) {
                        // buscar entrada un día antes
                        $oDateAux = clone $result->pinnedDateTime;
                        $oDateAux->subDay();
                        $oFoundRegistryI = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_IN);
                        if ($oFoundRegistryI != null) {
                            $newRow->sInDate = $oFoundRegistryI->date.' '.$oFoundRegistryI->time;
                            $newRow->inDate = $oFoundRegistryI->date;
                            $newRow->inDateTime = $oFoundRegistryI->date.'   '.$oFoundRegistryI->time;
    
                            $isNew = false;
                            $again = true;
                            $bFound = true;
                        }
                    }

                    if (! $bFound) {
                        $newRow->outDate = $result->variableDateTime->toDateString();
                        $newRow->outDateTime = $result->variableDateTime->format('Y-m-d   H:i:s');
                        $newRow->outDateTimeSch = $result->pinnedDateTime->format('Y-m-d   H:i:s');
                        $newRow->cutId = SDelayReportUtils::getCutId($result);

                        $isNew = true;
                        $again = false;
                        $newRow->comments = $newRow->comments."Falta entrada".",";
                    }
                }
            }

        }
        else {
            if ($newRow->outDate == null) {
                if ($newRow->inDate == null) {
                    $newRow->sInDate = $registry->date.' '.$registry->time;
                    $newRow->inDate = $registry->date;
                    $newRow->inDateTime = $registry->date.'   '.$registry->time;

                    $isNew = false;
                }
                else {
                    // falta salida
                    $bFound = false;
                    if ($registry->date == $sEndDate) {
                        // buscar entrada un día antes
                        $oDateAux = Carbon::parse($registry->date);
                        $oDateAux->addDay();
                        $oFoundRegistry = SDelayReportUtils::getRegistry($oDateAux->toDateString(), $idEmployee, \SCons::REG_OUT);
                        if ($oFoundRegistry != null) {
                            $isNew = false;
                            $bFound = true;
                            $again = true;
                        }
                    }

                    if (! $bFound) {
                        $newRow->comments = $newRow->comments."Falta salida".",";
                        $again = true;
                        $isNew = true;
                    }
                }
            }
        }

        if ($isNew) {
            if (SDelayReportUtils::isSunday($newRow)) {
                $newRow->isSunday = 1;
                // $newRow->others = 'PRIMA DOMINICAL, '.$newRow->others;
            }
            
            $lWorks = clone $qWorkshifts;
            $event = SDelayReportUtils::checkEvents($lWorks, $idEmployee, $newRow->inDate);

            if ($event != null) {
                $newRow->others = $event->td_name.', '.$newRow->others;
            }
        }

        $response = array();
        $response[] = $isNew;
        $response[] = $newRow;
        $response[] = $again;
        $response[] = $oFoundRegistry;

        return $response;
    }

    /**
     * Determina las horas extras correspondientes
     *
     * @param SRegistryRow $oRow
     * @param query_result $registry
     * @param query_result $result
     * 
     * @return SRegistryRow $oRow
     */
    private static function setMinsToRow($oRow = null, $registry = null, $result = null) {
        $oRow->outDate = $result->variableDateTime->toDateString();
        $oRow->outDateTime = $result->variableDateTime->format('Y-m-d   H:i:s');
        $oRow->outDateTimeSch = $result->pinnedDateTime->format('Y-m-d   H:i:s');
        $config = \App\SUtils\SConfiguration::getConfigurations();

        //Obtención de minutos de salida prematura
        $dateAux = clone $result->pinnedDateTime;
        $dateAux->subMinutes($config->toleranceMinutes);
        $earlyComp = SDelayReportUtils::compareDates($result->variableDateTime->toDateTimeString(), $dateAux->toDateTimeString());
        $oRow->diffMins = $earlyComp->delayMins;
        // $newRow->outDateTimeSch = $result->pinnedDateTime->toDateTimeString();

        //comparar hora entrada vs hora programada
        $sInDate = $oRow->sInDate;
        $sInSchedule = SDelayReportUtils::getScheduleIn($result);

        $comparison = SDelayReportUtils::compareDates($sInSchedule, $sInDate);
        //Determinar a partir de qué hora se toman horas extra
        $oDateExtra = null;
        if ($comparison->delayMins > 0) {
            $oDate = clone $result->pinnedDateTime;
            $oDateExtra = $oDate->addMinutes($comparison->delayMins);
        }
        else {
            $oDateExtra = $result->pinnedDateTime;
        }

        //duración de jornada con horas programadas
        $comparisonSched = SDelayReportUtils::compareDates($sInSchedule, $result->pinnedDateTime->toDateTimeString());
        // duración de jornada con horas de checadas
        $comparisonCheck = SDelayReportUtils::compareDates($sInDate, $result->variableDateTime->toDateTimeString());
        $oRow->cutId = SDelayReportUtils::getCutId($result);

        // Si cumple con las horas de trabajo requeridas (tomando en cuenta minutos de tolerancia)
        if ($comparisonCheck->delayMins >= ($comparisonSched->delayMins - $config->toleranceMinutes)) { // holgura
            // Se compara la nueva hora de salida de referencia cotra la hora de salida de la checada
            $resultN = SDelayReportUtils::compareDates($oDateExtra->toDateTimeString(), $result->variableDateTime->toDateTimeString());

            // se obtienen horas extras de la base de datos
            $oRow->overDefaultMins = SDelayReportUtils::getExtraTime($result);
            // se obtiene tiempo extra por turno mayor de 8 horas
            $oRow->overScheduleMins = SDelayReportUtils::getExtraTimeBySchedule($result);
            $oRow->overWorkedMins = $registry->is_overtime ? $resultN->delayMins : 0;
            $mins = ($oRow->overWorkedMins < 0 ? 0 : $oRow->overWorkedMins) 
                        + $oRow->overDefaultMins 
                        + $oRow->overScheduleMins;
            $oRow->delayMins = $mins;
            $oRow->extraHours = SDelayReportUtils::convertToHoursMins($mins);
        }
        else {
            $oRow->overDefaultMins = 0;
            $oRow->overScheduleMins = 0;
            $oRow->overWorkedMins = 0;
            $mins = 0;
            $oRow->delayMins = $mins;
            $oRow->extraHours = SDelayReportUtils::convertToHoursMins($mins);
        }

        return $oRow;
    }

    /**
     * Determina la hora de entrada programada en base al objeto recibido
     *
     * @param SDateComparison $oComparison
     * @return String "yyyy-MM-dd hh:mm:ss"
     */
    private static function getScheduleIn($oComparison) {
        $night = false;
        $sDate = "";
        if ($oComparison->auxScheduleDay != null) {
            $oAux = $oComparison->auxScheduleDay;
            $night = $oAux->is_night;
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

        $time = $oAux->entry;
        if ($night) {
            $oAuxDate = clone $oComparison->pinnedDateTime;
            $sDate = $oAuxDate->subDay()->toDateString();
        }
        else {
            $sDate = $oComparison->pinnedDateTime->toDateString();
        }

        return $sDate." ".$time;
    }

    /**
     * Devuelve el tiempo extra procedente de la base de datos,
     * correspondiente al tiempo que le corresponde por default
     *
     * @param SDateComparison $oComparison
     * @return int Minutos extra correspondientes
     */
    private static function getExtraTime($oComparison) {
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
    private static function getExtraTimeBySchedule($oComparison) {
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
        
        $mins = abs($comp->delayMins);
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
     * Obtiene la bandera de si se recorta entrada o salida
     *
     * @param SDateComparison $oComparison
     * @return int cut_id
     */
    private static function getCutId($oComparison) {
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
                                ->select('r.*', 'd.id AS dept_id', 'e.num_employee', 'e.name', 'e.is_overtime')
                                ->orderBy('employee_id', 'ASC')
                                ->orderBy('date', 'ASC')
                                ->orderBy('time', 'ASC');
                                // ->where('employee_id', '68');

        if (sizeof($lEmployees) > 0) {
            $registries = $registries->whereIn('e.id', $lEmployees);
        }

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
    private static function getRegistry($sDate, $iEmployee, $iType)
    {
        $registry = \DB::table('registers AS r')
                                ->join('employees AS e', 'e.id', '=', 'r.employee_id')
                                ->where('r.date', $sDate)
                                ->where('e.id', $iEmployee)
                                ->select('r.*', 'e.num_employee', 'e.name', 'e.is_overtime');

        if ($iType == \SCons::REG_IN) {
            $registry = $registry->orderBy('date', 'DESC')
                                ->orderBy('time', 'DESC');
        }
        else {
            $registry = $registry->orderBy('date', 'ASC')
                                ->orderBy('time', 'ASC');
        }

        $registry = $registry->get();

        foreach ($registry as $reg) {
            if ($iType == \SCons::REG_IN) {
                if ($reg->type_id == \SCons::REG_OUT) {
                    return null;
                }
                
                return $reg;
            }
            else {
                if ($reg->type_id = \SCons::REG_IN) {
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
                                        'td.name AS td_name', 
                                        'td.short_name', 
                                        'dwe.type_day_id')
                            ->where('dwe.is_delete', false)
                            ->where('w.is_delete', false)
                            ->where('e.is_delete', false)
                            ->whereBetween('wdd.date', [$startDate, $endDate]);

        if (sizeof($lEmployees) > 0) {
            $lWorkshifts = $lWorkshifts->whereIn('dwe.employee_id', $lEmployees);
        }

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
                        if ($assign->group_schedules_id == null) {
                            return SDelayReportUtils::compareTemplate($assign->schedule_template_id, $registry, $tReport);
                        }
                        else {
                            $res = SDelayReportUtils::getScheduleAssignGrouped($assign->group_schedules_id, $registry->date);
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

    private static function getScheduleAssignGrouped($group, $dateToCompare)
    {
        $oDtCompare = Carbon::parse($dateToCompare);
        $schedules = \DB::table('schedule_assign AS sa')
                            ->where('group_schedules_id', $group)
                            ->where('is_delete', false)
                            ->orderBy('order_gs', 'ASC')
                            ->get();
        
        $count = sizeof($schedules);
        $firstDate = Carbon::parse($schedules[0]->start_date);
        $secondDate = (clone $firstDate)->addDays(6);

        $search = true;
        while ($search) {
            for ($i = 0; $i < $count; $i++) {
                if ($oDtCompare->between($firstDate, $secondDate)) {
                    return $schedules[$i]->schedule_template_id;
                }
                $firstDate = (clone $firstDate)->addDays(7);
                $secondDate = (clone $firstDate)->addDays(6);
            }

            if ($oDtCompare->isAfter($secondDate)) {
                return null;
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
                                    'st.overtimepershift')
                            ->where('schedule_template_id', $templateId)
                            ->where('day_num', $day)
                            ->where('is_active', true)
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

        $comparison->delayMins = $mins;

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
    public static function checkSchedule($lWorkshifts, $idEmployee, $registry, $mType)
    {
        $lWEmployee = $lWorkshifts->where('e.id', $idEmployee)
                                    ->where('wdd.date', $registry->date)
                                    ->orderBy('wdd.created_at', 'DESC');
                                    
        $lWEmployee = $lWEmployee->get();
        
        if (sizeof($lWEmployee) == 0) {
            return null;
        }
        
        if ($mType == null) {
            return $lWEmployee;
        }

        $workshift = $lWEmployee[0];

        $workshiftDate = $registry->date.' '.($mType == \SCons::REP_DELAY ? $workshift->entry : $workshift->departure);

        $comparison = SDelayReportUtils::compareDates($workshiftDate, $registry->date.' '.$registry->time);
        $comparison->auxWorkshift = $workshift;
        
        return $comparison;
    }

    public static function checkEvents($lWorkshifts, $idEmployee, $date)
    {
        $lWEmployee = $lWorkshifts->where('e.id', $idEmployee)
                                    ->where('wdd.date', $date)
                                    ->where('dwe.type_day_id', '>', 1)
                                    ->orderBy('wdd.created_at', 'DESC');

        $lWEmployee = $lWEmployee->get();

        if (sizeof($lWEmployee) == 0) {
            return null;
        }

        $workshift = $lWEmployee[0];

        return $workshift;
    }

    /**
     * Busca un horario asignado al empleado para la fecha seleccionada, tanto en assigns como en 
     * workshifts
     *
     * @param String $startDate
     * @param String $endDate
     * @param int $idEmployee
     * @param [type] $registry
     * @param query $lWorkshifts
     * @param int $iRep [\SCons::REP_DELAY, \SCons::REP_HR_EX]
     * @return void
     */
    public static function getSchedule($startDate, $endDate, $idEmployee, $registry, $lWorkshifts, $iRep) {
        // checar horarios *******************************************************************
        $lAssigns = SDelayReportUtils::hasAnAssing($idEmployee, 0, $startDate, $endDate);

        if ($lAssigns != null) {
            $result = SDelayReportUtils::processRegistry($lAssigns, $registry, $iRep);

            if ($result != null) {
                if ($result->auxScheduleDay->is_active) {
                    // $day->prog_entry = $result->auxScheduleDay->entry;
                    // $day->prog_leave = $result->auxScheduleDay->departure;
                    
                    //  $day->is_absence = true;
                    return $result;
                }
            }
        }

        /**
         * busca el horario en base a las tablas de workshift
        */
        $result = SDelayReportUtils::checkSchedule($lWorkshifts, $idEmployee, $registry, $iRep);
        
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
            $oDate->addDays(1);
        }
        else {
            $oDate->subDays($iDay - 2);
        }

        while (SDateTimeUtils::dayOfWeek($oDate) != \SCons::WEEK_START_DAY) {
            $registry = (object) [
                'date' => $oDate->toDateString(),
                'time' => $time
            ];
            
            $res = SDelayReportUtils::getSchedule($oDate->toDateString(), $oDate->toDateString(), $idEmployee, $registry, clone $lWorkshifts, \SCons::REP_HR_EX);
            
            if ($res == null) {
                $oDate->addDays(1);
            }
            else {
                $res->oAuxDate = Carbon::parse($date.' '.$time);
                return $res;
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
}

?>
