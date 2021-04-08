<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use App\Models\register;
use Carbon\Carbon;
use DB;

class biostarController extends Controller
{
    public static function login()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $config = \App\SUtils\SConfiguration::getConfigurations();

        $client = new GuzzleClient([
            // Base URI is used with relative requests
            'base_uri' => $config->urlBiostar."/api/",
            // You can set any number of default request options.
            'timeout'  => 2.0,
            'headers' => $headers,
            'verify' => false
        ]);

        $body = '{
                "User": {
                "login_id": "admin",
                "password": "swaplicado10"
                }
            }';

        $r = $client->request('POST', 'login', [
            'body' => $body
        ]);

        if ($r->getStatusCode() == 200) {
            $sessionid = $r->getHeader('bs-session-id')[0];

            return $sessionid;
        }
        
        return null;
    }

    public static function getUsers()
    {
        $rez = biostarController::login();

        if ($rez == null) {
            return null;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'bs-session-id' => $rez
        ];

        $config = \App\SUtils\SConfiguration::getConfigurations();

        $client = new GuzzleClient([
            // Base URI is used with relative requests
            'base_uri' => $config->urlBiostar."/api/",
            // You can set any number of default request options.
            'timeout'  => 2.0,
            'headers' => $headers,
            'verify' => false
        ]);

        $body = "{ }";
        
        $r = $client->request('GET', 'users', [
            'body' => $body
        ]);

        $response = $r->getBody()->getContents();
        $data = json_decode($response);

        return $data;
    }

    public function indexUsersBiostar()
    {
        $data = biostarController::getUsers();

        $lUsers = [];
        foreach ($data->UserCollection->rows as $row) {
            if (($row->fingerprint_template_count > 0 && $row->face_count > 0) || $row->user_id == 1) {
                continue;
            }

            $usr = (object) [
                'id_user' => $row->user_id,
                'user_name' => $row->name,
                'has_fingerprint' => $row->fingerprint_template_count > 0,
                'has_face' => $row->face_count > 0
            ];

            $lUsers[] = $usr;
        }

        return view('biostar.indexhc')
                            ->with('lUsers', $lUsers);
    }

    public static function getEvents(){
        $rez = biostarController::login();

        if ($rez == null) {
            return null;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'bs-session-id' => $rez,
            'Accept-Encoding' => 'gzip, deflate, br'
        ];

        $config = \App\SUtils\SConfiguration::getConfigurations(); 
        
        
        $client = new GuzzleClient([
            // Base URI is used with relative requests
            'base_uri' => $config->urlBiostar."/api/",
            // You can set any number of default request options.
            'timeout'  => 2.0,
            'headers' => $headers,
            'verify' => false
        ]);
        
        $fecha_biostar = Carbon::parse($config->lastEventSyncDateTime);
        $fecha_biostar = $fecha_biostar->toISOString();
        $body = '{"Query": {"limit": 100000,"conditions": [{"column": "event_type_id.code","operator": 0,"values": ["4867"]},{"column": "datetime","operator": 5,"values": ["'.$fecha_biostar.'"]}],"orders": [{"column": "datetime","descending": false}]}}';
        //$body = json_encode($body);
        $r = $client->request('POST', 'events/search', [
            'body' => $body
        ]);
        $response = $r;
        $response = $r->getBody()->getContents();
        $data = json_decode($response);

        return $data;
    }
    
    public static function insertEvents(){
        $data = biostarController::getEvents();
        
        $lEvents = [];
        if($data->EventCollection->rows == ""){return 1;}
        foreach ($data->EventCollection->rows as $row) {

            $checada = (object) [
                'user_id' => $row->user_id->user_id,
                //'user_name' => $row->user_id->name,
                'date' => Carbon::parse($row->server_datetime)->toDateString(),
                'time' => Carbon::parse($row->server_datetime)->toTimeString(),
                'tna_key' => $row->tna_key
            ];

            $lEvents[] = $checada;
        }
        DB::beginTransaction();
        try{
            for( $i = 0 ; count($lEvents) > $i ; $i++){

                $employee_id = DB::table('employees')->where('biostar_id',$lEvents[$i]->user_id)->get();
                $register = new register();
                $register->employee_id = $employee_id[0]->id;
                $register->date = $lEvents[$i]->date;
                $register->time = $lEvents[$i]->time;
                $register->type_id = $lEvents[$i]->tna_key;
                $register->form_creation_id = 4;
                $register->save();  

                
            }
            
        } catch (\Exception $e) {
            DB::rollback(); 
            return 0;

        }
        DB::commit();
        $newDate = Carbon::now();
        \App\SUtils\SConfiguration::setConfiguration('lastEventSyncDateTime', $newDate->toDateTimeString());
        return 1;
    }
}
