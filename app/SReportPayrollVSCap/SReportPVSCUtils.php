<?php namespace App\SReportPayrollVSCap;

use App\Http\Controllers\prePayrollController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\earningController;
use App\Models\commentsControl;
use App\Models\empVsPayroll;
use App\Models\employees;
use App\SData\SOverJourneyCore;
use App\SUtils\SDateTimeUtils;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SIncidentsUtils;
use App\SUtils\SPrepayrollAdjustUtils;
use App\SUtils\SRegistryRow;
use App\SUtils\SReportsUtils;
use Carbon\Carbon;
use App\SUtils\SDateUtils;
use App\Models\cutCalendarQ;
use App\SData\SDataProcess;
use App\SUtils\SGenUtils;
use App\SReport\SJourneyReport;
use DB;

class SReportPVSCUtils{
    /**
      * Realiza el proceso de empatar checadas vs horarios programados y regresa una
      * lista de SRegistryRow con los datos correspondientes
      *
      * @param string $sStartDate
      * @param string $sEndDate
      * @param int $payWay [ 1: QUINCENA, 2: SEMANA]
      * @param int $tReport [\SCons::REP_DELAY, \SCons::REP_HR_EX]
      * @param array $lEmployees arreglo de ids de empleados

      * @return [SRegistryRow] (array)
      */
    public static function delayProcess($sStartDate, $sEndDate, $payWay, $aEmployees, $payroll)
    {
        $lEmployees = SGenUtils::toEmployeeIds(0, 0, null, $aEmployees->pluck('id')->toArray());
        SDataProcess::checkEvents();
        $comments = commentsControl::where('is_delete',0)->select('key_code','value')->get();

        // Filtrar empleados, solo aparecerán aquellos que hayan sido dados de alta antes de la fecha de inicio
        $lEmployees = SReportsUtils::filterEmployeesByAdmissionDate($lEmployees, $sEndDate, 'id');

        $data53 = SDataProcess::getSchedulesAndChecks($sStartDate, $sEndDate, $payWay, $lEmployees, $comments);

        $aEmployees = $lEmployees->pluck('id');
        $lWorkshifts = SDelayReportUtils::getWorkshifts($sStartDate, $sEndDate, $payWay, $aEmployees->toArray());
        // Rutina para verificación de renglones completos
        $lDataComplete = SDataProcess::completeDays($sStartDate, $sEndDate, $data53, $aEmployees->toArray(), $lWorkshifts, $comments);
        $lData53_2 = SDataProcess::addEventsDaysOffAndHolidays($lDataComplete, $lWorkshifts, $comments);
        
        $aEmployeeBen = $lEmployees->pluck('ben_pol_id', 'id');
        $lDataWithAbs = SDataProcess::addAbsences($lData53_2, $aEmployeeBen, $comments);

        // $aEmployeeOverTime = $lEmployees->pluck('is_overtime', 'id');
        $aEmployeeOverTime = $lEmployees->pluck('policy_extratime_id', 'id');
        $lDelay = clone $lDataWithAbs;

        $lDelay = SReportPVSCUtils::getDelayInfo($lDelay, $aEmployeeOverTime, $sEndDate, $comments);
        SReportPVSCUtils::saveDelayInfo($lDelay,$payroll);

        $lDeparture = SReportPVSCUtils::getDepartureInfo($lDataWithAbs, $aEmployeeOverTime, $sEndDate, $comments);
        SReportPVSCUtils::saveDepartureInfo($lDeparture,$payroll);

        $lTE = SReportPVSCUtils::getTEInfo($lDataWithAbs, $aEmployeeOverTime, $sEndDate, $comments);
        SReportPVSCUtils::saveTEInfo($lTE,$payroll);

    }

    public static function getDelayInfo($lData, $aEmployeeOverTime, $sEndDate, $comments = null){
        $lDataC = clone $lData;
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $workshifts = DB::table('workshifts')->where('is_delete',0)->get();
        $consumAdjs = [];
        foreach ($lDataC as $oRow) {
            $minutesCapAdjust = 0;
            $minutesPghAdjust = 0;
            $delayMins = 0;

            if ($oRow->hasChecks) {
                $cIn = false;
                $cOut = false;

                if (! $oRow->isSpecialSchedule) {
                    if ($oRow->hasCheckIn) {
                        $mayBeOverTime = false;
                        $cIn = SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $mayBeOverTime);
                    }
                    if ($oRow->hasCheckOut) {
                        //$mayBeOverTime = $aEmployeeOverTime[$oRow->idEmployee];
                        $mayBeOverTime = false;
                        $cOut = SDataProcess::isCheckSchedule($oRow->outDateTime, $oRow->outDateTimeSch, $mayBeOverTime);
                    }
                    if (($cIn && $cOut) || ! $oRow->hasSchedule) {
                        $oRow = SDataProcess::determineSchedule($oRow, $sEndDate, $workshifts);
                    }
                }

                // minutos de retardo
                $delayMins = SDataProcess::getDelayMins($oRow->inDateTime, $oRow->inDateTimeSch);
                //suma de los minutos de retardo en todo el periodo

                if ($delayMins > 0) {

                    // Ajuste de prenómina
                    $date = $oRow->inDate == null ? $oRow->inDateTime : $oRow->inDate;
                    $time = strlen($oRow->inDateTime) > 10 ? substr($oRow->inDateTime, -8) : null;
                    $adjs = SPrepayrollAdjustUtils::getAdjustsOfRow($date, $date, $oRow->idEmployee, \SCons::PP_TYPES['OR']);
                    $capAdjustsNormal = count(collect($adjs)->where('is_external',0)->where('minutes', 0));
                    $capAdjustsSpecial = count(collect($adjs)->where('is_external',0)->where('minutes', '>', 0));
                    $pghAdjustsNormal = count(collect($adjs)->where('is_external',1)->where('minutes', 0));
                    $pghAdjustsSpecial = count(collect($adjs)->where('is_external',1)->where('minutes', '>', 0));


                    if($capAdjustsNormal){
                        foreach ($adjs as $adj) {
                            if (! in_array($adj->id, $consumAdjs)) {
                                if ($adj->apply_to == 1) {
                                    if ($adj->dt_date == $date) {
                                        if ($time == $adj->dt_time) {
                                            $minutesCapAdjust += $delayMins;
                                        }
                                    }
                                }
                            }
                        }    
                    }else if($capAdjustsSpecial){
                        foreach ($adjs as $adj) {
                            if ($adj->apply_to == 1) {
                                if ($adj->dt_date == $date) {
                                    if ($time == $adj->dt_time) {
                                        if($adj->minutes > $delayMins){
                                            $minutesCapAdjust += $delayMins;
                                        }else{
                                            $minutesCapAdjust += $adj->minutes;
                                        }  
                                    }
                                }
                            }
                        }
                    }else if($pghAdjustsNormal){
                        foreach ($adjs as $adj) {
                            if (! in_array($adj->id, $consumAdjs)) {
                                if ($adj->apply_to == 1) {
                                    if ($adj->dt_date == $date) {
                                        if ($time == $adj->dt_time) {
                                            $minutesPghAdjust += $delayMins;
                                        }
                                    }
                                }
                            }
                        } 
                    }else if($pghAdjustsSpecial){
                        foreach ($adjs as $adj) {
                            if ($adj->apply_to == 1) {
                                if ($adj->dt_date == $date) {
                                    if ($time == $adj->dt_time) {
                                        if($adj->minutes > $delayMins){
                                            $minutesPghAdjust += $delayMins;
                                        }else{
                                            $minutesPghAdjust += $adj->minutes;
                                        } 
                                    }
                                }
                            }
                        }
                    }   
                } 
            }
            $oRow->delayMins = $delayMins;
            $oRow->minutesCapAdjust = $minutesCapAdjust;
            $oRow->minutesPghAdjust = $minutesPghAdjust;

        }    

        return $lDataC;
    }

    public static function saveDelayInfo($lData, $payroll){
        $employeeCompare = $lData[0]->idEmployee;
        $totalDelayOrinal = 0;
        $totalCap = 0;
        $totalPgh = 0;
        try{
            DB::beginTransaction();
            foreach ($lData as $oRow) {
                if ($oRow->idEmployee != $employeeCompare) {
                    $emppayroll = empVsPayroll::where('emp_id',$employeeCompare)->where('num_biweek',$payroll)->first();
                    if(!is_null($emppayroll)){
                        $emppayroll->time_delay_real = $totalDelayOrinal;
                        $emppayroll->time_delay_justified = $totalCap;
                        $emppayroll->time_delay_permission = $totalPgh;
                        $emppayroll->update();
                    }    
                    $totalDelayOrinal = 0;
                    $totalCap = 0;
                    $totalPgh = 0;
                    $employeeCompare = $oRow->idEmployee;
                }

                $totalDelayOrinal += $oRow->delayMins;
                $totalCap += $oRow->minutesCapAdjust;
                $totalPgh += $oRow->minutesPghAdjust;

            }

            $emppayroll = empVsPayroll::where('emp_id',$employeeCompare)->where('num_biweek',$payroll)->first();
            if(!is_null($emppayroll)){
                $emppayroll->time_delay_real = $totalDelayOrinal;
                $emppayroll->time_delay_justified = $totalCap;
                $emppayroll->time_delay_permission = $totalPgh;
                $emppayroll->update();
            }
            DB::commit();
        }catch(\Throwable $th){
            \Log::error($th);
            DB::rollBack();
        }

    }
    
    public static function getEmployeeReport(){
       $lEmployees = DB::table('employees')
                        ->whereIN('id',[1212])
                        ->get();

        return $lEmployees;
    }

    public static function getDepartureInfo($lData, $aEmployeeOverTime, $sEndDate, $comments = null){
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $workshifts = DB::table('workshifts')->where('is_delete',0)->get();
        $consumAdjs = [];
        foreach ($lData as $oRow) {
            if ($oRow->hasChecks) {
                $cIn = false;
                $cOut = false;

                if (! $oRow->isSpecialSchedule) {
                    if ($oRow->hasCheckIn) {
                        $mayBeOverTime = false;
                        $cIn = SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $mayBeOverTime);
                    }
                    if ($oRow->hasCheckOut) {
                        //$mayBeOverTime = $aEmployeeOverTime[$oRow->idEmployee];
                        $mayBeOverTime = false;
                        $cOut = SDataProcess::isCheckSchedule($oRow->outDateTime, $oRow->outDateTimeSch, $mayBeOverTime);
                    }
                    if (($cIn && $cOut) || ! $oRow->hasSchedule) {
                        $oRow = SDataProcess::determineSchedule($oRow, $sEndDate, $workshifts);
                    }
                }
                // minutos de salida anticipada
                if ($oRow->hasCheckOut) {
                    $minsPrematureOut = SDataProcess::getPrematureTime($oRow->outDateTime, $oRow->outDateTimeSch);
                    $oRow->minsPrematureOut = $minsPrematureOut;
                    $justifiedMins = 0;
                    $date = $oRow->outDate == null ? $oRow->outDateTime : $oRow->outDate;
                    $time = strlen($oRow->outDateTime) > 10 ? substr($oRow->outDateTime, -8) : null;
                    $adjs = SPrepayrollAdjustUtils::getAdjustForCase($oRow->outDate, $time, 2, \SCons::PP_TYPES['JSA'], $oRow->idEmployee);
                    if (count($adjs) > 0) {
                        foreach ($adjs as $adj) {
                            $justifiedMins += $adj->minutes;
                        }
                    }
                    if ($justifiedMins > $minsPrematureOut) {
                        $oRow->justifiedMins = $minsPrematureOut;
                    }else{
                        $oRow->justifiedMins = $justifiedMins;
                    }
                }
                else {
                    $oRow->justifiedMins = 0; 
                    $oRow->minsPrematureOut = 0;
                }
            }else{
                $oRow->justifiedMins = 0;  
                $oRow->minsPrematureOut = 0;  
            }
        }
        return $lData;
    }

    public static function saveDepartureInfo($lData, $payroll){
        $employeeCompare = $lData[0]->idEmployee;
        $totalPrematureOrinal = 0;
        $totalPgh = 0;
        try{
            DB::beginTransaction();
            foreach ($lData as $oRow) {
                if ($oRow->idEmployee != $employeeCompare) {
                    $emppayroll = empVsPayroll::where('emp_id',$employeeCompare)->where('num_biweek',$payroll)->first();
                    if(!is_null($emppayroll)){
                        $emppayroll->early_departure_original = $totalPrematureOrinal;
                        $emppayroll->early_departure_permission = $totalPgh;
                        $emppayroll->update();
                    }

                    $totalPrematureOrinal = 0;
                    $totalPgh = 0;
                    $employeeCompare = $oRow->idEmployee;
                }

                $totalPrematureOrinal += $oRow->minsPrematureOut;
                $totalPgh += $oRow->justifiedMins;

            }

            $emppayroll = empVsPayroll::where('emp_id',$employeeCompare)->where('num_biweek',$payroll)->first();
            if(!is_null($emppayroll)){
                $emppayroll->early_departure_original = $totalPrematureOrinal;
                $emppayroll->early_departure_permission = $totalPgh;
                $emppayroll->update();
            }
            DB::commit();
        }catch(\Throwable $th){
            DB::rollBack();
        }
    }
    public static function getTEInfo($lData, $aEmployeeOverTime, $sEndDate, $comments = null){
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $workshifts = \DB::table('workshifts')->where('is_delete',0)->get();
        $consumAdjs = [];
        foreach ($lData as $oRow) {
            if (! $oRow->workable &&
                    ($aEmployeeOverTime[$oRow->idEmployee] == \SCons::ET_POL_NEVER || 
                    $aEmployeeOverTime[$oRow->idEmployee] == \SCons::ET_POL_SOMETIMES)) {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;

                $oRow->overMinsTotal = 0;

                continue;
            }

            if ($oRow->hasChecks) {
                $cIn = false;
                $cOut = false;

                if (! $oRow->isSpecialSchedule) {
                    if ($oRow->hasCheckIn) {
                        $mayBeOverTime = false;
                        $cIn = SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $mayBeOverTime);
                    }
                    if ($oRow->hasCheckOut) {
                        //$mayBeOverTime = $aEmployeeOverTime[$oRow->idEmployee];
                        $mayBeOverTime = false;
                        $cOut = SDataProcess::isCheckSchedule($oRow->outDateTime, $oRow->outDateTimeSch, $mayBeOverTime);
                    }
                    if (($cIn && $cOut) || ! $oRow->hasSchedule) {
                        $oRow = SDataProcess::determineSchedule($oRow, $sEndDate, $workshifts);
                    }
                }

                // minutos de retardo
                $delayMins = SDataProcess::getDelayMins($oRow->inDateTime, $oRow->inDateTimeSch);
                if ($delayMins > 0) {
                    $hasDelay = true;
                    $minsAfterAdjs = $delayMins;

                    // Ajuste de prenómina
                    $date = $oRow->inDate == null ? $oRow->inDateTime : $oRow->inDate;
                    $time = strlen($oRow->inDateTime) > 10 ? substr($oRow->inDateTime, -8) : null;
                    $adjs = SPrepayrollAdjustUtils::getAdjustsOfRow($date, $date, $oRow->idEmployee, \SCons::PP_TYPES['OR']);
                    $normalAdjusts = count(collect($adjs)->where('minutes', 0));
                    if ($normalAdjusts > 0) {
                        foreach ($adjs as $adj) {
                            if (! in_array($adj->id, $consumAdjs)) {
                                if ($adj->apply_to == 1) {
                                    if ($adj->dt_date == $date) {
                                        if ($time == $adj->dt_time) {
                                            $hasDelay = false;
                                            $consumAdjs[] = $adj->id;
                                            $minsAfterAdjs = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $specialAdjusts = count(collect($adjs)->where('minutes', '>', 0));
                        $justifiedMins = 0;
                        if ($specialAdjusts > 0) {
                            foreach ($adjs as $adj) {
                                if ($adj->apply_to == 1) {
                                    if ($adj->dt_date == $date) {
                                        if ($time == $adj->dt_time) {
                                            $justifiedMins += $adj->minutes;
                                        }
                                    }
                                }
                            }
                        }

                        $minsAfterAdjs = $justifiedMins > $delayMins ? 0 : $delayMins - $justifiedMins;
                        $hasDelay = $minsAfterAdjs > 0;
                    }

                    if ($hasDelay) {
                        $oRow->entryDelayMinutes = $minsAfterAdjs;
                        $oRow->comments = $oRow->comments."Retardo. ";
                        if ($comments != null) {
                            if ($comments->where('key_code','entryDelayMinutes')->first()['value']) {
                                $oRow->isDayChecked = true;
                            }
                        }
                    }

                    if ($delayMins != $minsAfterAdjs) {
                        $oRow->justifiedDelayMins = $delayMins - $minsAfterAdjs;
                    }
                }
                else {
                    $oRow->entryDelayMinutes = 0;
                }

                // minutos de salida anticipada
                if ($oRow->hasCheckOut) {
                    $minsPrematureOut = SDataProcess::getPrematureTime($oRow->outDateTime, $oRow->outDateTimeSch);
                    $justifiedMins = 0;
                    $date = $oRow->outDate == null ? $oRow->outDateTime : $oRow->outDate;
                    $time = strlen($oRow->outDateTime) > 10 ? substr($oRow->outDateTime, -8) : null;
                    $adjs = SPrepayrollAdjustUtils::getAdjustForCase($oRow->outDate, $time, 2, \SCons::PP_TYPES['JSA'], $oRow->idEmployee);
                    if (count($adjs) > 0) {
                        foreach ($adjs as $adj) {
                            $justifiedMins += $adj->minutes;
                        }
                    }
                    $oRow->prematureOut = $justifiedMins > $minsPrematureOut ? 0 : ($minsPrematureOut - $justifiedMins);
                }
                else {
                    $oRow->prematureOut = null; 
                }

                $bWork8hr = true;
                $oRow->hasWorkedJourney8hr = SDataProcess::journeyCompleted($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch, $bWork8hr, $oRow->workJourneyMins);
                $bWork8hr = false;
                $bJorneyCompleted = SDataProcess::journeyCompleted($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch, $bWork8hr, $oRow->workJourneyMins);
                if ($bJorneyCompleted) {
                    $extendJourney = SDelayReportUtils::compareDates($oRow->inDateTimeSch, $oRow->outDateTimeSch);
                    if ($aEmployeeOverTime[$oRow->idEmployee] == 2 || ($aEmployeeOverTime[$oRow->idEmployee] == 3 && $extendJourney->diffMinutes > 480)) {
                        // minutos extra trabajados y filtrados por bandera de "genera horas extra"
                        $oRow->overWorkedMins += SDataProcess::getOverTime($oRow->inDateTime, $oRow->inDateTimeSch, $oRow->outDateTime, $oRow->outDateTimeSch);
                    }
                }
                else {
                    if (! $oRow->workable && $oRow->hasCheckIn && $oRow->hasCheckOut && $aEmployeeOverTime[$oRow->idEmployee] == \SCons::ET_POL_ALWAYS) {
                        $workedTime = SDelayReportUtils::compareDates($oRow->inDateTime, $oRow->outDateTime);
                        $workedMins = $workedTime->diffMinutes;

                        // si el tiempo trabajado es menor al máximo de tiempo configurado
                        if ($workedMins < $config->maxOvertimeJourneyMinutes && $workedMins > 0) {
                            $oRow->overWorkedMins += $workedMins;
                            $oRow->overDefaultMins = 0;
                            $oRow->overScheduleMins = 0;

                            $oRow->comments = $oRow->comments."Jornada TE. ";
                            if ($comments != null) {
                                if ($comments->where('key_code','isIncompleteTeJourney')->first()['value']) {
                                    $oRow->isDayChecked = true;
                                }
                            }
                            $oRow->isIncompleteTeJourney = true;

                            $date = $oRow->outDate == null ? $oRow->outDateTime : $oRow->outDate;
                            $time = strlen($oRow->outDateTime) > 10 ? substr($oRow->outDateTime, -8) : null;
                            $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['DHE'], $oRow->idEmployee);
                            $discountMins = 0;
                            if (count($adjs) > 0) {
                                foreach ($adjs as $adj) {
                                    $discountMins += $adj->minutes;
                                }
                            }

                            if ($oRow->overWorkedMins >= $discountMins) {
                                $oRow->overMinsByAdjs = - $discountMins;
                            }
                            else {
                                $oRow->overMinsByAdjs = - $oRow->overWorkedMins;
                            }

                            // si el día es domingo quita la prima
                            if (SDateTimeUtils::dayOfWeek($oRow->outDate) == Carbon::SUNDAY) {
                                $oRow->removeSunday = true;
                            }
                        }
                        else {
                            $oRow->overWorkedMins = 0;
                            $oRow->overDefaultMins = 0;
                            $oRow->overScheduleMins = 0;

                            if ($oRow->hasSchedule && $oRow->inDateTimeSch != null && $oRow->outDateTimeSch != null) {
                                $oRow->comments = $oRow->comments."Jornada incompleta. ";
                                $oRow->isOverJourney = false;
                            }
                        }
                    }
                    else {
                        $oRow->overWorkedMins = 0;
                        $oRow->overDefaultMins = 0;
                        $oRow->overScheduleMins = 0;
                        
                        if ($oRow->hasSchedule && $oRow->inDateTimeSch != null && $oRow->outDateTimeSch != null) {
                            $oRow->comments = $oRow->comments."Jornada incompleta. ";
                            $oRow->isOverJourney = false;
                        }
                    }
                }
            }
            else {
                $oRow->overWorkedMins = 0;
                $oRow->overDefaultMins = 0;
                $oRow->overScheduleMins = 0;

                $oRow->overMinsTotal = 0;
            }

            // Ajuste de prenómina
            $date = $oRow->outDate == null ? $oRow->outDateTime : $oRow->outDate;
            $time = strlen($oRow->outDateTime) > 10 ? substr($oRow->outDateTime, -8) : null;
            $adjs = SPrepayrollAdjustUtils::getAdjustForCase($date, $time, 2, \SCons::PP_TYPES['AHE'], $oRow->idEmployee);

            if (count($adjs) > 0) {
                $minsExtraByAdj = 0;
                foreach ($adjs as $adj) {
                    $minsExtraByAdj += $adj->minutes;
                }

                $oRow->overMinsByAdjs = $oRow->overMinsByAdjs + $minsExtraByAdj;
            }

            if ($oRow->hasChecks) {
                $cIn = false;
                $cOut = false;
                $adjIn = SPrepayrollAdjustUtils::hasTheAdjustType($oRow->adjusts, \SCons::PP_TYPES['JE']);
                $adjOut = SPrepayrollAdjustUtils::hasTheAdjustType($oRow->adjusts, \SCons::PP_TYPES['JS']);
                if ($oRow->hasCheckIn) {
                    $mayBeOverTime = false;
                    $cIn = SDataProcess::isCheckSchedule($oRow->inDateTime, $oRow->inDateTimeSch, $mayBeOverTime);
                    if ($cIn && !$adjIn) {
                        $oRow->comments = $oRow->comments."Entrada atípica. ";
                        if ($comments != null) {
                            if ($comments->where('key_code','isAtypicalIn')->first()['value']) {
                                $oRow->isDayChecked = true;
                            }
                        }
                        $oRow->isAtypicalIn = true;
                    }
                }
                if ($oRow->hasCheckOut) {
                    //$mayBeOverTime = $aEmployeeOverTime[$oRow->idEmployee];
                    $mayBeOverTime = false;
                    $cOut = SDataProcess::isCheckSchedule($oRow->outDateTime, $oRow->outDateTimeSch, $mayBeOverTime);
                    if ($cOut && !$adjOut) {
                        $oRow->comments = $oRow->comments."Salida atípica. ";
                        if ($comments != null) {
                            if ($comments->where('key_code','isAtypicalOut')->first()['value']) {
                                $oRow->isDayChecked = true;
                            }
                        }
                        $oRow->isAtypicalOut = true;
                    }
                }
                if (($cIn || $cOut) && (! $adjIn && ! $adjOut)) {
                    $oRow->comments = $oRow->comments."Revisar horario. ";
                    if ($comments != null) {
                        if ($comments->where('key_code','isCheckSchedule')->first()['value']) {
                            $oRow->isDayChecked = true;
                        }
                    }
                    $oRow->isCheckSchedule = true;
                }

                if ($oRow->isAtypicalOut && $oRow->isAtypicalIn) {
                    $oRow->overDefaultMins = 0;
                }
            }

            if ((($oRow->overWorkedMins + $oRow->overMinsByAdjs) >= 20) || (($oRow->overScheduleMins + $oRow->overMinsByAdjs) >= 60)) {
                if ($comments != null) {
                    if ($comments->where('key_code','overWorkedMins')->first()['value']) {
                        $oRow->isDayChecked = true;
                    }
                }
            }

            // suma de minutos extra totales.
            $oRow->overMinsTotal = $oRow->overWorkedMins + $oRow->overDefaultMins + $oRow->overScheduleMins + $oRow->overMinsByAdjs;
        }

        return $lData;
    }

    public static function saveTEInfo($lData, $payroll){
        $employeeCompare = $lData[0]->idEmployee;
        $totalTESTP = 0;
        $totalHorario = 0;
        $totalTrabajado = 0;
        $totalAjustado = 0;
        $totalTotal = 0;
        try{
            DB::beginTransaction();
            foreach ($lData as $oRow) {
                if ($oRow->idEmployee != $employeeCompare) {
                    
                    $emppayroll = empVsPayroll::where('emp_id',$employeeCompare)->where('num_biweek',$payroll)->first();
                    if(!is_null($emppayroll)){
                        $emppayroll->te_stps = $totalTESTP ;
                        $emppayroll->te_schedule = $totalHorario;
                        $emppayroll->te_work = $totalTrabajado;
                        $emppayroll->te_adjust = $totalAjustado;
                        $emppayroll->te_total = $totalTotal;
                        $emppayroll->update();
                    }
                    $totalTESTP = 0;
                    $totalHorario = 0;
                    $totalTrabajado = 0;
                    $totalAjustado = 0;
                    $totalTotal = 0;
                    $employeeCompare = $oRow->idEmployee;
                }

                $totalTESTP += $oRow->overDefaultMins;
                $totalTrabajado += $oRow->overWorkedMins;
                $totalHorario += $oRow->overScheduleMins;
                $totalAjustado += $oRow->overMinsByAdjs;;
                $totalTotal += $oRow->overMinsTotal;

            }

            $emppayroll = empVsPayroll::where('emp_id',$employeeCompare)->where('num_biweek',$payroll)->first();
            if(!is_null($emppayroll)){
                $emppayroll->te_stps = $totalTESTP ;
                $emppayroll->te_schedule = $totalHorario;
                $emppayroll->te_work = $totalTrabajado;
                $emppayroll->te_adjust = $totalAjustado;
                $emppayroll->te_total = $totalTotal;
                $emppayroll->update();
            }
            DB::commit();
        }catch(\Throwable $th){
            \Log::error($th);
            DB::rollBack();
        }
    }

    /**
     * En base a la configuración recibida procesa y envía el reporte de jornadas laborales.
     * Devuelve un string vacío si todo salió bien o un string con el error.
     * 
     * @param string $sConfiguration JSON con la configuración necesaria para la ejecución del reporte
     * {
        "$schema": "https://json-schema.org/draft/2020-12/schema",
        "type": "object",
        "properties": {
            "pay_type": {
            "type": "integer"
            },
            "back_prepayroll": {
            "type": "integer"
            },
            "companies": {
            "type": "array",
            "items": {
                "type": "integer"
            }
            },
            "areas": {
            "type": "array",
            "items": false
            },
            "departments_cap": {
            "type": "array",
            "items": {
                "type": "integer"
            }
            },
            "departments_siie": {
            "type": "array",
            "items": {
                "type": "integer"
            }
            },
            "employees": {
            "type": "array",
            "items": false
            },
        },
        "required": [
            "pay_type",
            "back_prepayroll",
            "companies",
            "areas",
            "departments_cap",
            "departments_siie",
            "employees"
        ]
        }
     * @param string $sReference fecha en la que estaba programada la tarea
     * 
     * @return string con el error si ocurrió alguno o vacío si todo salió OK
    */

    public static function manageTaskReport($sConfiguration, $sReference)
    {
        $sConfiguration = file_get_contents(base_path('tasks/payroll_info_cfg.json'));
        // Validar si la cadena recibida es un JSON
        if (! SJourneyReport::isJson($sConfiguration)) {
            return "Error, la configuración recibida no es un string JSON.";
        }

        $oConfiguration = json_decode($sConfiguration);

        // Si la configuración de tipo de pago no es correcta, retorna error
        if ($oConfiguration->pay_type == 0 || $oConfiguration->pay_type == "") {
            return "Error, el tipo de pago en la configuración no es válido.";
        }

        $sStartDate = "";
        $sEndDate = "";
        $sPayTypeText = "";
        try {
            // La referencia es un string con el tipo de pago _ id de corte (Ejem: Q_456)
            $numPP = substr($sReference, 2);
            if ($oConfiguration->pay_type == \SCons::PAY_W_Q) {
                $oCut = cutCalendarQ::find($numPP);
                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte con la referencia: " . $sReference;
                }
                $oDate = Carbon::parse($oCut->dt_cut);
                if ($oConfiguration->back_prepayroll > 0) {
                    $oDate->subDays(15 * $oConfiguration->back_prepayroll);
                }

                $lCuts = cutCalendarQ::where('dt_cut', '<=', $oDate->toDateString())
                                        ->where('is_delete', 0)
                                        ->orderBy('dt_cut', 'DESC')
                                        ->limit(2)
                                        ->get();

                if (count($lCuts) < 2) {
                    return "Error, no se encontró fecha de corte para el reporte programado.";
                }

                $sEndDate = $lCuts[0]->dt_cut;
                $sStartDate = Carbon::parse($lCuts[1]->dt_cut)->addDay()->toDateString();
                $sPayTypeText = "Quincena";
            }
            else {
                $oCut = week_cut::find($numPP);
                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte con la referencia: " . $sReference;
                }
                $oDate = Carbon::parse($oCut->fin);
                if ($oConfiguration->back_prepayroll > 0) {
                    $oDate->subDays(7 * $oConfiguration->back_prepayroll);
                }

                $oCut = week_cut::where('fin', '<=', $oDate->toDateString())
                            ->orderBy('fin', 'DESC')
                            ->first();

                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte para el reporte programado.";
                }

                $sStartDate = $oCut->ini;
                $sEndDate = $oCut->fin;
                $sPayTypeText = "Semana";
            }

            $lData = SyncController::syncronizeWithPayroll($numPP, $oConfiguration->pay_type,$sConfiguration);
            $insertado = earningController::saveEarningFromJSON($lData,$numPP);
            if ($insertado) {
                $lEmployee = employees::whereIn('id', $oConfiguration->employees)->get();
                SReportPVSCUtils::delayProcess($sStartDate, $sEndDate, $oConfiguration->pay_type, $lEmployee, $numPP);
            }

            return "";
        }
        catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();    
        }
    }
}

?>