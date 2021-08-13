<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\employees;

class PhotosController extends Controller
{
    public function index($idEmployee = 0)
    {
        // $jsonString = file_get_contents(base_path('response_photos_from_siie.json'));
        $client = new Client([
            'base_uri' => '192.168.1.233:9001',
            'timeout' => 10.0,
        ]);

        if ($idEmployee > 0) {
            $employee = employees::find($idEmployee);
        }
        else {
            if (\Auth::user()->employee_id > 0) {
                $employee = employee::find(\Auth::user()->employee_id);
            }
            else {
                return redirect()->back()->withError('No hay asignado un empleado para el usuario actual.');
            }
        }

        if ($employee == null) {
            return redirect()->back()->withError('No se encontró el empleado.');
        }

        if (! $employee->external_id > 0) {
            return redirect()->back()->withError('No hay asignado un empleado externo.');
        }
        
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
