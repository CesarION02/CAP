<?php

namespace App\Http\Controllers;

use App\Models\SCapLock;
use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\SData\SCutoffDates;

class SyncController extends Controller
{
    public static function toSyncronize()
    {
        // \App\SUtils\SConfiguration::addConfiguration('lastSyncDateTime', '2020-04-01 00:00:00');
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $correcto = SyncController::syncronizeWithERP($config->lastSyncDateTime);
        $resp = SCutoffDates::processCutoffDates($config->lastSyncDateTime);
        $sincronizado = \App\Http\Controllers\biostarController::insertEvents();
        $sincronizado = \App\Http\Controllers\biostarController::insertDevices();

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
}
