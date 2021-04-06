<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

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

        return $data;
    }

    public function indexUsersBiostar()
    {
        $data = $this->getUsers();

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
}
