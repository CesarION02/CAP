<?php

namespace App\Http\Controllers;

use App\Models\SCapLock;
use App\Models\employees;
use App\SReportPayrollVSCap\SReportPVSCUtils;
use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\SData\SCutoffDates;
use DB;

class SyncController extends Controller
{
    public static function toSyncronize()
    {
        // \App\SUtils\SConfiguration::addConfiguration('lastSyncDateTime', '2020-04-01 00:00:00');
        $config = \App\SUtils\SConfiguration::getConfigurations();

        //$correcto = SyncController::syncronizeWithERP($config->lastSyncDateTime);
        //$resp = SCutoffDates::processCutoffDates($config->lastSyncDateTime);
        //$sincronizado = \App\Http\Controllers\biostarController::insertEvents();
        //$sincronizado = \App\Http\Controllers\biostarController::insertDevices();

        $sincronizado = 1;
        if ($sincronizado != 0) {
            return redirect()->back()->with('mensaje', 'Sincronizado BioStar');
        }
        else {
            return redirect()->back()->with('mensaje', 'No se pudo sincronizar BioStar');
        }
    }

    public static function syncronizeWithERP($lastSyncDate = "")
    {
        $oLock = null;
        try {
            /**
             * Consulta si hay un candado activo de tipo sincronización
             */
            $oLock = SCapLock::where('lock_type', 'sincronizacion')
                                ->where('is_locked', true)
                                ->where('is_delete', 0)
                                ->first();

            if (! is_null($oLock )) {
                $oReleaseAt = Carbon::parse($oLock->got_at)->addSeconds($oLock->timer);
                /**
                 * Si el candado activo ya debia estar liberado, lo libera y sigue con el proceso
                 */
                if ($oReleaseAt->lessThan(Carbon::now())) {
                    $oLock->is_locked = false;
                    $oLock->completion_code = 501;
                    $oNow = Carbon::now('UTC');
                    $oNow->tz = new \DateTimeZone('-6:00');
                    $oLock->released_at = $oNow->toDateTimeString();
                    $oLock->save();
                }
                else {
                    return 1;
                }
            }

            /**
             * Crea un nuevo candado con un timer por default de 5 min
             */
            $oLock = new SCapLock();
            $oNow = Carbon::now('UTC');
            $oNow->tz = new \DateTimeZone('-6:00');
            $oLock->got_at = $oNow->toDateTimeString();
            $oLock->lock_type = 'sincronizacion';
            $oLock->is_locked = true;
            $oLock->user_id = \Auth::user()->id;
            $oLock->save();

            // $jsonString = file_get_contents(base_path('response_from_siie.json'));
            $config = \App\SUtils\SConfiguration::getConfigurations();

            $client = new Client([
                'base_uri' => $config->urlSyncCAPLink,
                'timeout' => 10.0,
            ]);
        
            $response = $client->request('GET', 'getInfoERP/' . $lastSyncDate);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);

            $deptRhCont = new DeptsRhController();
            $deptRhCont->saveRhDeptsFromJSON($data->departments);

            $jobCont = new JobRhController();
            $jobCont->saveJobsFromJSON($data->positions);

            $empCont = new employeeController();
            $empCont->saveEmployeesFromJSON($data->employees);

            $fdyCont = new fdyController();
            $fdyCont->saveFDYFromJSON($data->fdys);

            $holidayCont = new holidayController();
            $holidayCont->saveHolidaysFromJSON($data->holidays);

            $absCont = new incidentController();
            $absCont->saveAbsencesFromJSON($data->absences);

            $prepayCont = new prePayrollController();
            $prepayCont->saveCutCalendarFromJSON($data->cuts);

            $newDate = Carbon::now('UTC');
            $newDate->tz = new \DateTimeZone('-6:00');
            $newDate->subMinutes(30);

            \App\SUtils\SConfiguration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());

            /**
             * Una vez terminado el proceso libera el candado con el timestamp actual
             */
            $oLock->is_locked = false;
            $oLock->completion_code = 200;
            $oNow = Carbon::now('UTC');
            $oNow->tz = new \DateTimeZone('-6:00');
            $oLock->released_at = $oNow->toDateTimeString();
            $oLock->save();

            return 1;
        }
        catch (\Exception $e) {
            \Log::error($e);

            if ($oLock != null) {
                /**
                 * Libera el candado si hubo algún error
                 */
                $oLock->is_locked = false;
                $oLock->completion_code = 500;
                $oNow = Carbon::now('UTC');
                $oNow->tz = new \DateTimeZone('-6:00');
                $oLock->released_at = $oNow->toDateTimeString();
                $oLock->save();
            }

            return 0;
        }
    }

    public function dateSyncView(){
        return view('sync.syncView');
    }

    public function dateSyncProcess(Request $request){
        $sincronizado = \App\Http\Controllers\biostarController::insertEvents($request->date);
        if ($sincronizado != 0) {
            return redirect()->back()->with('mensaje', 'Sincronizado BioStar');
        }
        else {
            return redirect()->back()->with('mensaje', 'No se pudo sincronizar BioStar');
        }       
    }

    public static function syncronizeWithPayroll($numPayroll,$type,$sConfiguration)
    {
        $aConfiguration = json_decode($sConfiguration);
        $lEmployeesAreas = DB::table('employees as e')
                        ->join('departments as d', 'd.id', '=', 'e.department_id')
                        ->whereIn('d.area_id', $aConfiguration->areas)
                        ->where('e.is_active', 1)
                        ->where('e.is_delete', 0)
                        ->get();

        $lEmployeesDepartments = DB::table('employees as e')
                    ->whereIn('e.department_id', $aConfiguration->departments)
                    ->where('e.is_active', 1)
                    ->where('e.is_delete', 0)
                    ->get();

        $lEmployeesEmps = DB::table('employees as e')
                    ->whereIn('id', $aConfiguration->employees)
                    ->where('e.is_active', 1)
                    ->where('e.is_delete', 0)
                    ->get();

        $lEmployees = $lEmployeesAreas->merge($lEmployeesDepartments)->merge($lEmployeesEmps);
        $lEmployee = $lEmployees->unique('id');
        $payroll = DB::table('hrs_prepay_cut')
                        ->where('id',$numPayroll)
                        ->first();

        $rows = [];
        foreach($lEmployee as $emp){
            if(isset($emp->external_id)){
                $company = DB::table('companies')->where("id", $emp->company_id)->first();
                $row = [
                    'id_emp' => $emp->external_id,
                    'company_id' => $company->external_id,
                ];
            
                array_push($rows, $row);
            }
        }
        if ($type == 1){
            $arrJson = [
                'year' => $payroll->year,
                'num' => $payroll->num,
                'type_pay' => 2,
                'rows' => $rows 
            ];
        }else{
            $arrJson = [
                'year' => $payroll->year,
                'num' => $payroll->num,
                'type_pay' => 1,
                'rows' => $rows 
            ];
        }
        
        $client = new Client([
            'base_uri' => 'http://127.0.0.1:9001',
            'timeout' => 30.0,
        ]);
        $jsonPrueba = json_encode($arrJson);
        $response = $client->request('GET', 'getInfoPayroll/' . json_encode($arrJson));
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $jsonString;
    }

}
