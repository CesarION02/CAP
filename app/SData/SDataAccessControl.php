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
        $lAbsences = prePayrollController::searchAbsence($id, $dtDate);

        return $lAbsences;
    }

    public static function getEvents($id, $dtDate)
    {
        $lWorkshifts = SDelayReportUtils::getWorkshifts($dtDate, $dtDate, 0, [$id]);
        $lWorks = clone $lWorkshifts;
        $events = SDelayReportUtils::checkEvents($lWorks, $id, $dtDate);

        if ($events == null) {
            return [];
        }

        $lEvents = [];
        foreach ($events as $event) {
            $typeId = $event->type_day_id;

            $oEvent = new SAuxEvent();
            if ($typeId == \SCons::T_DAY_NORMAL) {
                continue;
            }

            $oEvent->dtDate = $dtDate;
            $oEvent->typeId = $typeId;
            
    
            $text = "";
    
            switch ($typeId) {
                case \SCons::T_DAY_INHABILITY:
                    $text = "Incapacidad";
                    break;
    
                case \SCons::T_DAY_VACATION:
                    $text = "Vacaciones";
                    break;
    
                case \SCons::T_DAY_HOLIDAY:
                    $text = "Festivo";
                    break;
    
                case \SCons::T_DAY_DAY_OFF:
                    break;
    
                default:
                    # code...
                    break;
            }
    
            $oEvent->typeName = $text;
            
            $lEvents[] = $oEvent;
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

}