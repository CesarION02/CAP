<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use App\Jobs\ProcessCheckNotification;
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

        //$filterType = $request->filter_users == null ? 1 : $request->filter_users;

        $usrCollection = collect($data->UserCollection->rows);

        $usrCollection = $usrCollection->where('user_id', '!=', 1);
        
        //if ($filterType != 0) {
        //    $usrCollection = $usrCollection->filter(function ($value, $key) {
        //        return $value->fingerprint_template_count == 0 || $value->face_count == 0;
        //    });
        //}
        
        $lUsers = [];
        $idUsers = [];
        foreach ($usrCollection as $row) {
            $usr = (object) [
                'id_user' => $row->user_id,
                'user_name' => $row->name,
                'has_fingerprint' => $row->fingerprint_template_count > 0,
                'has_face' => $row->face_count > 0,
                'has_card' => $row->card_count > 0
            ];
            $idUsers [] = $row->user_id;

            $lUsers[] = $usr;
        }
        $userOutBiostar = DB::table('employees')->where('biostar_id',null)->where('is_active',1)->where('department_id','!=',100)->get();
        $contador = count($lUsers);
        foreach ($userOutBiostar AS $row) {
            $usr = (object) [
                'id_user' => 'na',
                'user_name' => $row->name,
                'has_fingerprint' => 0,
                'has_face' => 0,
                'has_card' => 0
            ];
            
            $lUsers[$contador] = $usr;
            $contador++;
        }


        return view('biostar.indexhc')
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

    public static function getEvents($fecha = 0){
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
        if($fecha != 0){
            $fecha_biostar = Carbon::parse($fecha);
            $fecha_biostar = $fecha_biostar->toISOString();
        }else{
            $fecha_biostar = Carbon::parse($config->lastEventSyncDateTime);
            $fecha_biostar = $fecha_biostar->toISOString();
        }
        
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
                    "4865","4867","4097"
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
        // $jsonString = file_get_contents(base_path('response_from_biostar.json'));
        // $data = json_decode($jsonString);
        
        $lEvents = [];
        if($data->EventCollection->rows == ""){return 1;}
        foreach ($data->EventCollection->rows as $row) {
            $fecha = Carbon::parse($row->datetime);
            $fecha->setTimezone('America/Mexico_City');

            $checada = (object) [
                'user_id' => $row->user_id->user_id,
                //'user_name' => $row->user_id->name,
                'date' => Carbon::parse($fecha)->toDateString(),
                'time' => Carbon::parse($fecha)->toTimeString(),
                'tna_key' => $row->tna_key,
                'biostar_id'=> $row->id
            ];

            $lEvents[] = $checada;
        }

        $lRegisters = [];
        DB::beginTransaction();
        try{
            for( $i = 0 ; count($lEvents) > $i ; $i++){
                $revision = $lEvents[$i];
                $employee_id = DB::table('employees')->where('biostar_id',$lEvents[$i]->user_id)->where('is_active',1)->get();
                if(count($employee_id) == 0){
                    continue;
                }
                $repetido = DB::table('registers')->where('biostar_id',$lEvents[$i]->biostar_id)->where('employee_id',$employee_id[0]->id)->where('date',$lEvents[$i]->date)->where('is_delete',0)->get();
                if(count($repetido) != 0){
                    continue;
                }
                $register = new register();
                $register->employee_id = $employee_id[0]->id;
                $register->date = $lEvents[$i]->date;
                $register->time = $lEvents[$i]->time;
                $register->type_id = $lEvents[$i]->tna_key;
                $register->date_original = $lEvents[$i]->date;
                $register->time_original = $lEvents[$i]->time;
                $register->type_original = $lEvents[$i]->tna_key;
                $register->form_creation_id = 4;
                $register->biostar_id = $lEvents[$i]->biostar_id;
                $register->save();  

                if ($register->type_id == 1) {
                    $lRegisters[] = $register;
                }
            }
            
        } catch (\Exception $e) {
            DB::rollback(); 
            return 0;

        }
        DB::commit();

        $newDate = Carbon::now();
        $newDate->subHour(2);
        \App\SUtils\SConfiguration::setConfiguration('lastEventSyncDateTime', $newDate->toDateTimeString());
        
        foreach ($lRegisters as $register) {
            try {
                dispatch(new ProcessCheckNotification($register->employee_id, $register->date.' '.$register->time, "Biostar"));
            }
            catch (\Throwable $th) {
                \Log::error($th->getMessage());
            }
        }
        
        return 1;
    }
}
