<?php namespace App\SUtils;

/**
 * Estructura Utilizada para los renglones de los reporte de 
 * retardos y horas extras
 */
class SRegistryRow {
    public $idEmployee;
    public $numEmployee;
    public $employee;
    public $external_id;
    public $employeeAreaId;
    public $inDate;
    public $inDateTime;
    public $inDateTimeSch;
    public $inDateTimeNoficial;
    public $outDate;
    public $outDateTime;
    public $outDateTimeSch;
    public $scheduleText;
    public $outDateTimeNoficial;
    public $entryDelayMinutes;
    public $prematureOut;
    public $diffMins;
    public $delayMins;
    public $overDefaultMins;
    public $overScheduleMins;
    public $overWorkedMins;
    public $overMinsByAdjs;
    public $adjusts;
    public $overMinsTotal;
    public $cutId;
    public $extraHours;
    public $workedTime;
    public $hasAbsence;
    public $hasAbsenceAndDayOffWorked;
    public $workable;
    public $isIncompleteTeJourney;
    public $removeSunday;
    public $isSunday;
    public $isDayOff;
    public $isHoliday;
    public $extraDouble;
    public $extraDoubleMins;
    public $extraTriple;
    public $extraTripleMins;
    public $extraDoubleNoficial;
    public $extraDoubleMinsNoficial;
    public $extraTripleNoficial;
    public $extraTripleMinsNoficial;
    public $dayInhability;
    public $dayVacations;
    public $events;
    public $eventsText;
    public $hasAssign;
    public $hasChecks;
    public $hasSchedule;
    public $isSpecialSchedule;
    public $isOnSchedule;
    public $workJourneyMins;
    public $hasWorkedJourney8hr;
    public $isOverJourney;
    public $overtimeCheckPolicy;
    public $isDayRepeated;
    public $scheduleFrom;
    public $hasCheckOut;
    public $hasCheckIn;
    public $isModifiedIn;
    public $isModifiedOut;
    public $isAtypicalIn;
    public $isAtypicalOut;
    public $isCheckSchedule;
    public $isTypeDayChecked;
    public $hasAdjust;
    public $work_dayoff;
    public $others;
    public $comments;
    public $isDayChecked;
    public $departmentName;
    

    function __construct()
    {
        $this->idEmployee = 0;
        $this->numEmployee = 0;
        $this->employee = 0;
        $this->external_id = 0;
        $this->employeeAreaId = 0;
        $this->inDate = null;
        $this->inDateTime = null;
        $this->inDateTimeSch = null;
        $this->inDateTimeNoficial = null;
        $this->outDate = null;
        $this->outDateTime = null;
        $this->outDateTimeSch = null;
        $this->scheduleText = "";
        $this->outDateTimeNoficial = null;
        $this->entryDelayMinutes = 0;
        $this->prematureOut = null;
        $this->diffMins = null;
        $this->delayMins = 0;
        $this->overDefaultMins = 0;
        $this->overScheduleMins = 0;
        $this->overWorkedMins = 0;
        $this->overMinsByAdjs = 0;
        $this->adjusts = [];
        $this->overMinsTotal = null;
        $this->cutId = null;
        $this->extraHours = "00:00";
        $this->hasAbsence = false;
        $this->hasAbsenceAndDayOffWorked = false;
        $this->workable = true;
        $this->isIncompleteTeJourney = false;
        $this->removeSunday = false;
        $this->isSunday = 0;
        $this->isDayOff = 0;
        $this->isHoliday = 0;
        $this->extraDouble = "00:00";
        $this->extraDoubleMins = 0;
        $this->extraTriple = "00:00";
        $this->extraTripleMins = 0;
        $this->extraDoubleNoficial = "00:00";
        $this->extraDoubleMinsNoficial = 0;
        $this->extraTripleNoficial = "00:00";
        $this->extraTripleMinsNoficial = 0;
        $this->workedTime = 0;
        $this->dayInhability = 0;
        $this->dayVacations = 0;
        $this->events = [];
        $this->eventsText = "";
        $this->hasAssign = false;
        $this->hasChecks = true;
        $this->hasSchedule = true;
        $this->isSpecialSchedule = false;
        $this->isOnSchedule = true;
        $this->workJourneyMins = 0;
        $this->hasWorkedJourney8hr = false;
        $this->isOverJourney = false;
        $this->overtimeCheckPolicy = 2;
        $this->isDayRepeated = false;
        $this->scheduleFrom = 0;
        $this->hasCheckOut = true;
        $this->hasCheckIn = true;
        $this->isModifiedIn = false;
        $this->isModifiedOut = false;
        $this->isAtypicalIn = false;
        $this->isAtypicalOut = false;
        $this->isCheckSchedule = false;
        $this->isTypeDayChecked = false;
        $this->hasAdjust = false;
        $this->work_dayoff = 0;
        $this->others = "";
        $this->comments = "";
        $this->isDayChecked = false;
        $this->departmentName = "";
    }
}

?>