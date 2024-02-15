<?php

namespace App\SUtils;

use Illuminate\Support\Arr;
use GuzzleHttp\Client;
use GuzzleHttp\Request;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class SPghUtils{
    public static function loginToPGH(){
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => '*/*'
        ];
    
        $client = new Client([
            'base_uri' => '127.0.0.1/GHPort/public/api/',
            'timeout' => 30.0,
            'headers' => $headers,
            'verify' => false
        ]);
    
        $body = '{"username":"adminLocal","password":"123456"}';
        
        $request = new \GuzzleHttp\Psr7\Request('POST', 'login', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);
    
        return $data;
    }
}
?>