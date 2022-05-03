<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SData\SDataAccessControl;
use App\SUtils\SAccessControlData;
use App\Models\AccessLog;
use Validator;

use Carbon\Carbon;

use App\Jobs\ProcessCheckNotification;

class AccessControlController extends Controller
{
    public function getEmployees(Request $request)
    {
        $lEmployees = \DB::table('employees')
                        ->where('is_active', true)
                        ->where('is_delete', false)
                        ->select('id', 'name', 'num_employee', 'external_id', 'is_active', 'is_delete')
                        ->orderBy('name', 'ASC')
                        ->get();

        if (sizeof($lEmployees) > 0) {
            $oData = new SAccessControlData();

            $oData->employees = $lEmployees;
            return json_encode($oData);
        }

        return json_encode([]);
    }

    public function getIdEmployeeByNumber($numEmployee)
    {
        $id = \DB::table('employees')
                ->where('num_employee', $numEmployee)
                // ->where('way_register_id', 2)
                ->select('id')
                ->take(1)
                ->get();

        if (sizeof($id) > 0) {
            return $id[0]->id;
        }

        return -1;
    }

    public function getInfo($idEmp, $dtDate, $time, $nextDays)
    {
        $oData = new SAccessControlData();

        $oData->employee = SDataAccessControl::getEmployee($idEmp);

        if ($oData->employee == null) {
            return $oData;
        }

        $oData->absences = SDataAccessControl::getAbsences($idEmp, $dtDate);
        $oData->events = SDataAccessControl::getEvents($idEmp, $dtDate);
        $oData->schedule = SDataAccessControl::getSchedule($idEmp, $dtDate, $time);
        $oData->nextSchedule = SDataAccessControl::getNextSchedule($idEmp, $dtDate, $nextDays);

        return $oData;
    }

    public function getInfoByEmployeeNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dt_date' => 'required',
            'dt_time' => 'required',
            'num_emp' => 'required',
            'next_days' => 'required',
            'mins_in' => 'required',
            'mins_out' => 'required',
        ]);

        $numEmployee = $request->num_emp;
        $dtDate = $request->dt_date;
        $time = $request->dt_time;
        $nextDays = $request->next_days;
        $minsIn = $request->mins_in;
        $minsOut = $request->mins_out;

        $idEmp = $this->getIdEmployeeByNumber($numEmployee);

        if ($idEmp < 0) {
            return json_encode(new SAccessControlData());
        }

        return $this->processInfo($idEmp, $dtDate, $time, $nextDays, $minsIn, $minsOut, "NÚMERO");
    }

    public function getAllInfoById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dt_date' => 'required',
            'dt_time' => 'required',
            'id_emp' => 'required',
            'next_days' => 'required',
            'mins_in' => 'required',
            'mins_out' => 'required',
        ]);

        $idEmp = $request->id_emp;
        $dtDate = $request->dt_date;
        $time = $request->dt_time;
        $nextDays = $request->next_days;
        $minsIn = $request->mins_in;
        $minsOut = $request->mins_out;

        return $this->processInfo($idEmp, $dtDate, $time, $nextDays, $minsIn, $minsOut, "HUELLA");
    }

    private function processInfo($idEmp, $dtDate, $time, $nextDays, $minsIn, $minsOut, $sSource)
    {
        $oData = $this->getInfo($idEmp, $dtDate, $time, $nextDays);

        if ($oData->employee == null) {
            $oResData = clone $oData;

            $oResData->absences = null;
            $oResData->events = null;
            $oResData->authorized = false;
            $oResData->message = "No se encontró el empleado";
            
            return json_encode($oResData);
        }

        $res = SDataAccessControl::isAuthorized($oData, $idEmp, $dtDate, $time, $minsIn, $minsOut);

        $oResData = clone $oData;

        $oResData->absences = null;
        $oResData->events = null;
        $oResData->authorized = $res[0];
        $oResData->message = $res[1];

        $schIn = null;
        $schOut = null;
        if ($oResData->schedule != null) {
            $schIn = $oResData->schedule->inDateTimeSch;
            $schOut = $oResData->schedule->outDateTimeSch;
        }

        $this->saveAccessLog($idEmp, $dtDate.' '.$time, $minsIn, $minsOut, $sSource, $oResData->authorized, $oResData->message, $schIn, $schOut);

        dispatch(new ProcessCheckNotification($idEmp, $dtDate.' '.$time));

        return json_encode($oResData);
    }

    private function saveAccessLog($idEmp, $dtTimeLog, $minsIn, $minsOut, $sSource, $authorized, $message, $schIn, $schOut)
    {
        $oLog = new AccessLog();

        $oLog->employee_id = $idEmp;
        $oLog->dt_time_log = $dtTimeLog;
        $oLog->mins_in = $minsIn;
        $oLog->mins_out = $minsOut;
        $oLog->source = $sSource;
        $oLog->is_authorized = $authorized;
        $oLog->message = $message;
        $oLog->sch_in_dt_time = $schIn;
        $oLog->sch_out_dt_time = $schOut;

        $oLog->save();
    }
}
