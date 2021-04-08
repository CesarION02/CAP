<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use App\Models\employees;

class biostarController extends Controller
{
    public function login()
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
    public function logout($sessionId)
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
        $rez = $this->login();

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

        $this->logout($rez);

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
}
