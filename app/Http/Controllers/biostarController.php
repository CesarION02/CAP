<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use App\Models\register;
use App\Models\employees;
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

    /**
     * Cerrar sesión
     *
     * @return void
     */
    public static function logout($sessionId)
    {
        $headers = [
                'Content-Type' => 'application/json',
                'bs-session-id' => $sessionId
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

        $r = $client->request('POST', 'logout');
    }

    public function getUsers()
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

        biostarController::logout($rez);

        return $data;
    }

    public function indexUsersBiostar(Request $request)
    {
        $data = $this->getUsers();

        $filterType = $request->filter_users == null ? 1 : $request->filter_users;

        $usrCollection = collect($data->UserCollection->rows);

        $usrCollection = $usrCollection->where('user_id', '!=', 1);
        
        if ($filterType != 0) {
            $usrCollection = $usrCollection->filter(function ($value, $key) {
                return $value->fingerprint_template_count == 0 || $value->face_count == 0;
            });
        }
        
        $lUsers = [];
        foreach ($usrCollection as $row) {
            $usr = (object) [
                'id_user' => $row->user_id,
                'user_name' => $row->name,
                'has_fingerprint' => $row->fingerprint_template_count > 0,
                'has_face' => $row->face_count > 0,
                'has_card' => $row->card_count > 0
            ];

            $lUsers[] = $usr;
        }

        return view('biostar.indexhc')
                            ->with('filterType', $filterType)
                            ->with('lUsers', $lUsers);
    }

    /**
     * Modificación del id de biostar correspondiente al empleado
     *
     * @param Request $request
     * 
     * @return void
     */
    public function updateBiostarId(Request $request)
    {
        $data = json_decode($request->emp_row);

        employees::where('id', $data->id)
                    ->update(
                            [
                                'biostar_id' => $data->biostar_id > 0 ? $data->biostar_id : null,
                            ]
                        );

        return json_encode("OK");
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
            'timeout'  => 5.0,
            'headers' => $headers,
            'verify' => false
        ]);
        
        $fecha_biostar = Carbon::parse($config->lastEventSyncDateTime);
        $fecha_biostar = $fecha_biostar->toISOString();
        //$body = '{"Query": {"limit": 100000,"conditions": [{"column": "event_type_id.code","operator": 0,"values": ["4867"]},{"column": "datetime","operator": 5,"values": ["'.$fecha_biostar.'"]}],"orders": [{"column": "datetime","descending": false}]}}';
        // Reservada para traer la información del checador nuevo de planta. 
        $body = '{
            "Query": {
              "limit": 10000000,
              "conditions": [
                {
                  "column": "event_type_id.code",
                  "operator": 2,
                  "values": [
                    "4865","4867"
                  ]
                },
                {
                  "column": "datetime",
                  "operator": 5,
                  "values": [
                    "'.$fecha_biostar.'"
                  ]
                },
                {
                  "column":"device_id.id",
                  "operator": 2,
                  "values": [
                    "545406209","542390428"
                  ]
                }
              ],
              "orders": [
                {
                  "column": "datetime",
                  "descending": false
                }
              ]
            }
          }';
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
                $revision = $lEvents[$i];
                $employee_id = DB::table('employees')->where('biostar_id',$lEvents[$i]->user_id)->get();
                if(count($employee_id) == 0){
                    continue;
                }
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
