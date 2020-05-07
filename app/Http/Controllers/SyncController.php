<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;

class SyncController extends Controller
{
    public function toSyncronize()
    {
        // \App\SUtils\SConfiguration::addConfiguration('lastSyncDateTime', '2020-04-01 00:00:00');
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $this->syncronizeWithERP($config->lastSyncDateTime);

        return redirect('/')->with('mensaje', 'Sincronizado');
    }

    public function syncronizeWithERP($lastSyncDate = "")
    {
        // $jsonString = file_get_contents(base_path('response_from_siie.json'));
        $client = new Client([
            'base_uri' => 'localhost:8080',
            'timeout' => 10.0,
        ]);

        $response = $client->request('GET', 'getInfoERP/2019-01-01 00:00:00');
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

        $newDate = Carbon::now();
        $newDate->subMinutes(30);

        \App\SUtils\SConfiguration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());
    }
}
