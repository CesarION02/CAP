<?php namespace App\SData;

use Carbon\Carbon;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SAuxEvent;
use App\SUtils\SAuxSchedule;
use App\Http\Controllers\prePayrollController;

class SDataAccessControl {

    public static function getEmployee($id)
    {
        $employee = \DB::table('employees')
                    ->where('id', $id)
                    ->select('id', 'name', 'num_employee', 'external_id', 'is_active', 'is_delete')
                    ->get();

        if (sizeof($employee) > 0) {
            return $employee[0];
        }

        return null;
    }

    public static function getAbsences($id, $dtDate)
    {
        $lAbsences = \DB::table('incidents AS i')
                        ->join('type_incidents AS ti', 'i.type_incidents_id', '=', 'ti.id')
                        ->where('employee_id', $id)
                        ->whereRaw("'" . $dtDate . "' BETWEEN start_date AND end_date")
                        ->select('i.external_key', 'i.nts', 'ti.name AS type_name')
                        ->where('i.is_delete', false)
                        ->whereNotIn('ti.id', [14, 15])
                        ->orderBy('i.id', 'ASC')
                        ->get();

        return $lAbsences;
    }

    public static function getAllowedAbsences($id, $dtDate)
    {
        $lAbsences = \DB::table('incidents AS i')
                        ->join('type_incidents AS ti', 'i.type_incidents_id', '=', 'ti.id')
                        ->where('employee_id', $id)
                        ->whereRaw("'" . $dtDate . "' BETWEEN start_date AND end_date")
                        ->select('i.external_key', 'i.nts', 'ti.name AS type_name')
                        ->where('i.is_delete', false)
                        ->whereIn('ti.id', [14, 15])
                        ->orderBy('i.id', 'ASC')
                        ->get();

        return $lAbsences;
    }

    public static function getEvents($id, $dtDate)
    {
        $lEvents = [];
        $holidays = SDataProcess::getHolidays($id, $dtDate);

        if ($holidays != null) {
            $num = sizeof($holidays);
    
            if ($num > 0) {
                foreach ($holidays as $holiday) {
                    $oEvent = new SAuxEvent();
                    $oEvent->dtDate = $dtDate;
                    $oEvent->typeId = \SCons::T_DAY_HOLIDAY;
                    $oEvent->typeName = $holiday->name;
                    
                    $lEvents[] = $oEvent;
                }
            }
        }


        return $lEvents;
    }

    public static function getSchedule($id, $dtDate, $time)
    {
        $lWorkshifts = SDelayReportUtils::getWorkshifts($dtDate, $dtDate, 0, [$id]);

        $registry = (object) [
            'date' => $dtDate,
            'time' => $time,
            'type_id' => 1
        ];
        
        $result = SDelayReportUtils::getSchedule($dtDate, $dtDate, $id, $registry, clone $lWorkshifts, \SCons::REP_DELAY);

        if ($result == null) {
            return null;
        }

        $oSchedule = new SAuxSchedule();

        $oSchedule->isSpecialSchedule = $result->auxIsSpecialSchedule;
        $oSchedule->inDateTimeSch = $result->pinnedDateTime->toDateTimeString();
        $oSchedule->outDateTimeSch = SDataAccessControl::getScheduleOut($result);

        return $oSchedule;
    }

    public static function getNextSchedule($id, $dtDate, $nextDays)
    {
        $oDate = Carbon::parse($dtDate);
        for ($i = 0; $i < $nextDays; $i++) {
            $oDate->addDay();
            $oSchedule = SDataAccessControl::getSchedule($id, $oDate->toDateString(), "12:00:00");

            if ($oSchedule != null) {
                return $oSchedule;
            }
        }

        return null;
    }

    /**
     * Determina la hora de entrada programada en base al objeto recibido
     *
     * @param SDateComparison $oComparison
     * @return String "yyyy-MM-dd hh:mm:ss"
     */
    public static function getScheduleOut($oComparison) {
        $night = false;
        $sDate = "";
        if ($oComparison->auxScheduleDay != null) {
            $oAux = $oComparison->auxScheduleDay;
            $night = $oAux->is_night;
        }
        else {
            if ($oComparison->auxWorkshift != null) {
                $oAux = $oComparison->auxWorkshift;
                $night = $oAux->is_night;
            }
            else {
                return 0;
            }
        }

        $time = $oAux->departure;
        if ($night) {
            $oAuxDate = clone $oComparison->pinnedDateTime;
            $sDate = $oAuxDate->addDay()->toDateString();
        }
        else {
            $sDate = $oComparison->pinnedDateTime->toDateString();
        }

        return $sDate." ".$time;
    }

    /**
     * Determina si el empleado tiene autorizado el ingreso al sistema
     *
     * @param [type] $oData
     * @param [type] $id
     * @param [type] $dtDate
     * @param [type] $time
     * @param [type] $inMins
     * @param [type] $outMins
     * @return boolean
     */
    public static function isAuthorized($oData = null, $id, $dtDate, $time, $inMins, $outMins)
    {
        $result = [];
        $reason = "";

        if (! $oData->employee->is_active || $oData->employee->is_delete) {
            $reasons = "El empleado está desactivado en el sistema";
            
            $result[0] = false;
            $result[1] = $reasons;
            
            return $result;
        }

        // Si el empleado tiene incidencias programadas
        if ($oData->absences != null && count($oData->absences) > 0) {
            $reason = "";
            foreach ($oData->absences as $abs) {
                $reason = $reason == "" ? $abs->type_name : ($reason . ", " . $abs->type_name);
            }
            
            $reasons = "El empleado tiene incidencias: " . $reason . " para el día de hoy";
            
            $result[0] = false;
            $result[1] = $reasons;
            
            return $result;
        }

         // Si el empleado tiene eventos programados
         if ($oData->events != null && count($oData->events) > 0) {
            foreach ($oData->events as $event) {
                $reason = $reason == "" ? $event->typeName : ($reason.", ".$event->typeName);
            }

            $reasons = "El empleado tiene programado: " . $reason . " para el día de hoy";
            $result[0] = false;
            $result[1] = $reasons;
            
            return $result;
        }
        
        // si el empleado tiene un horario
        if ($oData->schedule != null) {
            // Si el empleado está o no en su horario
            if (SDataAccessControl::isOnShift($oData->schedule->inDateTimeSch, $oData->schedule->outDateTimeSch,  $dtDate . ' ' . $time, $inMins, $outMins)) {
                $result[0] = true;
                $result[1] = "Autorizado";
            
                return $result;
            }
            else {
                $result[0] = false;
                $result[1] = "Fuera del horario permitido. Revise horario";
            
                return $result;
            }
        }
        else {
            /**
             * Incidencias permitidas
             */
            $allowedAbs = SDataAccessControl::getAllowedAbsences($id, $dtDate);

            if (count($allowedAbs) > 0) {
                $result[0] = true;
                $reason = "El empleado tiene ";
                foreach ($allowedAbs as $abs) {
                    $reason = $reason.$abs->type_name;
                }

                $result[1] = $reason;

                return $result;
            }

            $result[0] = false;
            $result[1] = "El empleado no tiene horario asignado para el día de hoy";
            
            return $result;
        }
    }

    /**
     * Determina si la fecha/hora en la que el empleado checó están dentro del horario del empleado
     *
     * @param string $inDateTimeSch
     * @param string $outDateTimeSch
     * @param string $dateTime
     * @param integer $inMins Minutos de tolerancia previos a la hora de entrada (cuántos minutos puede entrar antes)
     * @param integer $outMins Minutos de tolerancia de salida, (cuántos minutos después de su horario de salida se le permite la entrada)
     * 
     * @return boolean
     */
    private static function isOnShift($inDateTimeSch, $outDateTimeSch, $dateTime, $inMins = 0, $outMins = 0)
    {
        $oDateTime = Carbon::parse($dateTime);
        $oInDateTimeSch = Carbon::parse($inDateTimeSch);
        $oOutDateTimeSch = Carbon::parse($outDateTimeSch);

        $oInDateTimeSch->subMinutes($inMins); 
        $oOutDateTimeSch->addMinutes($outMins);

        $res = $oDateTime->between($oInDateTimeSch, $oOutDateTimeSch, true);

        return $res;
    }

}