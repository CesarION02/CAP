<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SyncController extends Controller
{
    public static function toSyncronize()
    {
        // \App\SUtils\SConfiguration::addConfiguration('lastSyncDateTime', '2020-04-01 00:00:00');
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $correcto = SyncController::syncronizeWithERP($config->lastSyncDateTime);

        $sincronizado = \App\Http\Controllers\biostarController::insertEvents();

        if ($sincronizado != 0) {
            return redirect()->back()->with('mensaje', 'Sincronizado BioStar');
        }
        else {
            return redirect()->back()->with('mensaje', 'No se pudo sincronizar BioStar');
        }
    }

    public static function syncronizeWithERP($lastSyncDate = "")
    {
        // $jsonString = file_get_contents(base_path('response_from_siie.json'));
        $client = new Client([
            'base_uri' => '192.168.1.233:9000',
            'timeout' => 10.0,
        ]);

        try {
            $response = $client->request('GET', 'getInfoERP/' . $lastSyncDate);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);

            // dd($data);
            $deptRhCont = new DeptsRhController();
            $deptRhCont->saveRhDeptsFromJSON($data->departments);

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

            $newDate = Carbon::now();
            $newDate->subMinutes(30);

            \App\SUtils\SConfiguration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());

            return 1;
        }
        catch (RequestException $e) {
            return 0;
        }
    }
}
