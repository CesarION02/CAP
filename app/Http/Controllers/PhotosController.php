<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\employees;

class PhotosController extends Controller
{
    public function index($idEmployee)
    {
        // $jsonString = file_get_contents(base_path('response_photos_from_siie.json'));
        $client = new Client([
            'base_uri' => '192.168.1.233:9001',
            'timeout' => 10.0,
        ]);

        $employee = employees::find('id', $idEmployee);
        
        $response = $client->request('GET', 'getPhotoInfo/' . $employee->external_id);
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        // dd($data);

        $employees = employees::all();
        $employees = $employees->keyBy('external_id');

        foreach ($data->photos as $row) {
            $emp = $employees[$row->idEmployee];

            $row->name = $emp->name;
        }

        return view('photosemployee.index')->with('lPhotos', $data->photos);
    }
}
