<?php

namespace App\Http\Controllers;

use App\SUtils\SRegistryRow;
use App\SUtils\SuserVsDevice;
use App\Models\User;
use App\Models\device;
use App\Models\UserVsDevice;
use DB;

use Illuminate\Http\Request;

class userVsDevicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $devices = DB::table('user_vs_device')
                        ->join('users','user_vs_device.user_id', '=', 'users.id')
                        ->join('devices','user_vs_device.device_id', '=', 'devices.id')
                        ->where('user_vs_device.is_delete',0)
                        ->orderBy('users.id')
                        ->select('users.name AS nombreEmpleado','devices.name AS dn','users.id AS id')
                        ->get();

        $comparacion = 0;
        $nameUser = '';
        $Sdevices = '';
        $contadorDevices=0;
        $Adevice = [];

        foreach($devices as $device){
            if($nameUser == ''){
                $nameUser = $device->nombreEmpleado;    
            }
            if($comparacion == $device->id){
                $nameUser = $device->nombreEmpleado;
                if( $Sdevices == '' ){
                    $Sdevices = $device->dn;
                }else{
                    $Sdevices = $Sdevices.', '.$device->dn;
                }
            }else{
                if($comparacion != 0){
                    $userReg = new SuserVsDevice();
                    $userReg->nameEmployee = $nameUser;
                    $userReg->devices = $Sdevices;
                    $userReg->idUser = $comparacion;
                    $Adevice[$contadorDevices] = $userReg;
                    $Sdevices = '';
                    $contadorDevices++;
                }
                $comparacion = $device->id;
                $Sdevices = $device->dn;
            }
            
        }
        if( count($devices) != 0 ){
            $userReg = new SuserVsDevice();
            $userReg->nameEmployee = $nameUser;
            $userReg->devices = $Sdevices;
            $userReg->idUser = $comparacion;
            $Adevice[$contadorDevices] = $userReg;
        }
        
        return view('uservsdevices.index')->with('datas',$Adevice);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        $users = User::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $devices = device::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');

        return view('uservsdevices.create')->with('users',$users)->with('devices',$devices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(isset($request->devices)){
            if(!(count($request->devices) > 0)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }else{
            return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
        }
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }
        foreach($request->devices as $device){
            $repetidos = DB::table('user_vs_device')
                            ->where('user_id',$request->usuario)
                            ->where('device_id',$device)
                            ->get();
            
            if( count($repetidos) == 0 ){
                $register = new UserVsDevice();
                $register->user_id = $request->usuario;
                $register->device_id = $device;
                $register->updated_by = session()->get('user_id');
                $register->created_by = session()->get('user_id');
                $register->is_delete = 0;

                $register->save();
            }
            
        }
        return redirect('uservsdevice')->with('mensaje','Se asignaron los dispositivos correctamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $users = User::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');
        $devices = device::where('is_delete','0')->orderBy('name','ASC')->pluck('id','name');

        $datas = DB::table('user_vs_device')
            ->join('devices', 'user_vs_device.device_id','=','devices.id')
            ->join('users', 'user_vs_device.user_id', '=', 'users.id')
            ->where('user_vs_device.is_delete',0)
            ->where('user_vs_device.user_id',$id)
            ->select('users.id AS idUser', 'devices.id AS idDevice', 'users.name AS name')
            ->get();

        return view('uservsdevices.edit')->with('devices',$devices)->with('users',$users)->with('datas',$datas);  
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(isset($request->devices)){
            if(!(count($request->devices) > 0)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }else{
            return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
        }
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return redirect()->back()->withErrors('Debe llenar todos los campos del formulario');
            }
        }
        $borrar = UserVsDevice::where('user_id',$id)->delete();
        foreach($request->devices as $device){
            $register = new UserVsDevice();
            $register->user_id = $id;
            $register->device_id = $device;
            $register->updated_by = session()->get('user_id');
            $register->created_by = session()->get('user_id');
            $register->is_delete = 0;

            $register->save();
        }
        return redirect('uservsdevice')->with('mensaje','Se actualizaron los dispositivos correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        if ($request->ajax()) {
            $registros = DB::table('user_vs_device')
                            ->where('user_id',$id)
                            ->select('id AS id')
                            ->get();
            foreach($registros as $registro){
                $dgu = UserVsDevice::find($registro->id);
                $dgu->is_delete = 1;
                $dgu->save();
            }                
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
