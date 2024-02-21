<?php

namespace App\Http\Controllers;

use App\SUtils\SDataShowUser;
use Illuminate\Http\Request;
use App\Models\Admin\rol;
use App\Models\employees;
use App\Models\User;
use App\SUtils\SPghUtils;
use GuzzleHttp\Client;
use DB;

class userController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $iFilter = $request->ifilter == 0 ? 1 : $request->ifilter;
        $eFilter = $request->efilter == 0 ? 1 : $request->efilter;

        switch ($iFilter) {
            case 1:
                if($eFilter == 1){
                    $datas = User::where('is_delete','0')->where('employee_id','!=','null')->orderBy('name')->get();
                    $datas->each(function($datas){
                        $datas->employee;
                    });
                }else{
                    $datas = User::where('is_delete','0')->whereNull('employee_id')->orderBy('name')->get();
                    $datas->each(function($datas){
                        $datas->employee;
                    });
                }
                
                break;
            case 2:
                if($eFilter == 1){
                    $datas = User::where('is_delete','1')->where('employee_id','!=','null')->orderBy('name')->get();
                    $datas->each(function($datas){
                        $datas->employee;
                    });
                }else{
                    $datas = User::where('is_delete','1')->whereNull('employee_id')->orderBy('name')->get();
                    $datas->each(function($datas){
                        $datas->employee;
                    });    
                }
                break;
            
            default:
                if($eFilter == 1){
                    $datas = User::where('employee_id','!=','null')->orderBy('id')->get();
                    $datas->each(function($datas){
                        $datas->employee;
                    });
                }else{
                    $datas = User::whereNull('employee_id')->orderBy('id')->get();
                    $datas->each(function($datas){
                        $datas->employee;
                    });    
                }
                break;
        }

        
        return view('user.index', compact('datas'))->with('iFilter',$iFilter)->with('eFilter',$eFilter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $employees = employees::orderBy('name','ASC')->where('is_delete', 0)->pluck('id','name');
        $type = 1;
        return view('user.create', compact('employees'))->with('type',$type);
    }

    public function create_with_global()
    {
        $json = '[{"id": 1,"name": "CARMONA FIGUEROA, EDWIN OMAR","num": 990,"external": 3338, "email": "cesar.i@swaplicado.com.mx", "contrasena": 1234},{"id": 2,"name": "Espinoza Lopez, Daniel","num": 326,"external": 1904, "email": "cesar.i@swaplicado.com.mx","contrasena": 1234}]';
        $uGlobales = json_decode($json);
        $employees = employees::orderBy('name','ASC')->where('is_active',1)->where('is_delete', 0)->pluck('num_employee','name');
        $data = SPghUtils::loginToPGH();
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $data->token_type.' '.$data->access_token
        ];
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->pghApiRoute,
            'timeout' => 30.0,
            'headers' => $headers
        ]);   
        $body = '{"company":"'.$config->capIdSystem.'"}';
        $request = new \GuzzleHttp\Psr7\Request('GET', 'getPendingUser', $headers,$body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();

        $uGlobales = json_decode($jsonString);



        return view('user.create_global')->with('uGlobales',$uGlobales->data)->with('employees',$employees);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if( $request->passwordnu != $request->password ){
            return \Redirect::back()->withErrors(['Error', 'Las contraseña no es igual']);
        }

        if( $request->employee_id == 0){
            return \Redirect::back()->withErrors(['Error', 'Se debe seleccionar un empleado asociado']);    
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        if ($request->employee_id != 0){
            $user->employee_id = $request->employee_id;
        }
        $user->is_delete = 0;
        $user->updated_by = session()->get('user_id');
        $user->created_by = session()->get('user_id');
        $user->save();

        return redirect('user')->with('mensaje', 'Usuario creado con exito');
    }

    public function save_with_global(Request $request){
        try {


            DB::beginTransaction();
            $user = new User();
            $user->name = $request->fname;
            $user->email = $request->email;
            $user->password = bcrypt($request->fpassword);
            if ($request->femployee_id != 0){
                $employee = DB::table('employees')->where('num_employee',$request->femployee_id)->first();
                $user->employee_id = $employee->id;
            }
            $user->is_delete = 0;
            $user->updated_by = session()->get('user_id');
            $user->created_by = session()->get('user_id');
            $user->save();
            //DB::commit();

            //enviar el id del usuario a global data
            $data = SPghUtils::loginToPGH();
            if($data->status == 'success'){
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*',
                    'Authorization' => $data->token_type.' '.$data->access_token
                ];
            
                $config = \App\SUtils\SConfiguration::getConfigurations();
                $client = new Client([
                    'base_uri' => $config->pghApiRoute,
                    'timeout' => 30.0,
                    'headers' => $headers
                ]);
            
                $body = json_encode(['user' => $user, 'id_global' => $request->fglobal, 'id_system' => $config->capIdSystem]);
                
                $request = new \GuzzleHttp\Psr7\Request('POST', 'insertUserVsSystem', $headers, $body);
                $response = $client->sendAsync($request)->wait();
                $jsonString = $response->getBody()->getContents();
                $data = json_decode($jsonString);

                if($data->status == 'success'){
                    DB::commit();
                    return redirect('user')->with('mensaje', 'Usuario creado con exito');
                }else{
                    DB::rollBack();
                    return redirect('user')->with('mensaje', 'No se pudo crear el usuario');
                }

            }else{
                DB::rollBack();
                return redirect('user')->with('mensaje', 'No se pudo crear el usuario');
            }
            
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error($th);
            return redirect('user')->with('mensaje', 'No se pudo crear el usuario');
        }

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
        $data = User::findOrFail($id);
        $employees = employees::orderBy('id','ASC')->pluck('id','name');
        $type = 2;
        return view('user.edit', compact('data'))->with('employees',$employees)->with('type',$type);
    }

    public function editGlobal($id){
        $data = User::findOrFail($id);
        $employees = employees::orderBy('id','ASC')->pluck('id','name');
        $rol = DB::table('user_rol')->where('user_id',$data->id)->first();
        return view('user.editGlobal', compact('data'))->with('employees',$employees)->with('rol',$rol);   
    }

    public function editMyGlobal($id){
        $data = User::findOrFail($id);
        $employees = employees::orderBy('id','ASC')->pluck('id','name');
        $rol = DB::table('user_rol')->where('user_id',$data->id)->first();
        return view('user.editMyGlobal', compact('data'))->with('employees',$employees)->with('rol',$rol); 
    }

    public function updateGlobal(Request $request){
        if (! (\Hash::check($request->prevpass, \Auth::user()->password))) {
            return \Redirect::back()->withErrors(['Error', 'Las contraseña anterior no corresponde con nuestros registros']); 
        }
        if($request->newpass != $request->confirmpass){
            return \Redirect::back()->withErrors(['Error', 'La nueva contraseña no es igual al campo confirmar contraseña']);   
        }
        try{
            DB::beginTransaction();
            $user = User::findOrFail($request->id_user);
            if($request->rol == 1 || $request->rol == 3 || $request->rol == 8 ){
                $user->email = $request->email;
                $user->name = isset($request->us) ? $request->us : $user->name;
                $user->password = \Hash::make($request->newpass);
            }else{
                $user->password = \Hash::make($request->newpass);
            }
            $user->save();

            if(isset($request->us)){
                $user->username = $request->us;
            }else{
                $user->username = $user->name;
            }
            $user->pass = \Hash::make($request->newpass);  
            //enviar el id del usuario a global data
            $data = SPghUtils::loginToPGH();
            if($data->status == 'success'){
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*',
                    'Authorization' => $data->token_type.' '.$data->access_token
                ];
            
                $config = \App\SUtils\SConfiguration::getConfigurations();
                $client = new Client([
                    'base_uri' => $config->pghApiRoute,
                    'timeout' => 30.0,
                    'headers' => $headers
                ]);
            
                $body = json_encode(['user' => $user, 'fromSystem' => $config->capIdSystem]);
                
                $request = new \GuzzleHttp\Psr7\Request('POST', 'updateGlobal', $headers, $body);
                $response = $client->sendAsync($request)->wait();
                $jsonString = $response->getBody()->getContents();
                $data = json_decode($jsonString);

                if($data->status == 'success'){
                    DB::commit();
                    return redirect()->route('editar_usuario_global', $user->id)->with('mensaje', 'Usuario actualizado con exito');
                }else{
                    DB::rollBack();
                    return \Redirect::back()->withErrors(['Error', 'El usuario no se puedo actualizar, trate más tarde']); 
                }
            }else{
                DB::rollBack();
                return \Redirect::back()->withErrors(['Error', 'El usuario no se puedo actualizar, trate más tarde']);  
            }     
        } catch (\Throwable $th) {
            $success = false;
            DB::rollBack();
            return \Redirect::back()->withErrors(['Error', 'El usuario no se puedo actualizar, trate más tarde']);  
        }  
            
    }

    public function updateMyGlobal(Request $request){
        if (! (\Hash::check($request->prevpass, \Auth::user()->password))) {
            return \Redirect::back()->withErrors(['Error', 'Las contraseña anterior no corresponde con nuestros registros']); 
        }
        if($request->newpass != $request->confirmpass){
            return \Redirect::back()->withErrors(['Error', 'La nueva contraseña no es igual al campo confirmar contraseña']);   
        }

        try{
            DB::beginTransaction();
            $user = User::findOrFail($request->id_user);
            if($request->rol == 1 || $request->rol == 3 || $request->rol == 8 ){
                $user->email = $request->email;
                $user->name = isset($request->us) ? $request->us : $user->name;
                $user->password = \Hash::make($request->newpass);
            }else{
                $user->password = \Hash::make($request->newpass);
            }
            $user->save();
            if(isset($request->us)){
                $user->username = $request->us;
            }else{
                $user->username = $user->name;
            }
            $user->pass = \Hash::make($request->newpass); 
            //DB::commit();
            //enviar el id del usuario a global data
            $data = SPghUtils::loginToPGH();
            if($data->status == 'success'){
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*',
                    'Authorization' => $data->token_type.' '.$data->access_token
                ];
            
                $config = \App\SUtils\SConfiguration::getConfigurations();
                $client = new Client([
                    'base_uri' => $config->pghApiRoute,
                    'timeout' => 30.0,
                    'headers' => $headers
                ]);
            
                $body = json_encode(['user' => $user, 'fromSystem' => $config->capIdSystem]);
                
                $request = new \GuzzleHttp\Psr7\Request('POST', 'updateGlobal', $headers, $body);
                $response = $client->sendAsync($request)->wait();
                $jsonString = $response->getBody()->getContents();
                $data = json_decode($jsonString);
                if($data->status == 'success'){
                    DB::commit();
                    return redirect()->route('editar_mi_usuario_global', $user->id)->with('mensaje', 'Usuario actualizado con exito');
                }else{
                    DB::rollBack();
                    // return redirect('user.editMyGlobal')->with('mensaje', 'El usuario no se puedo actualizar, trate más tarde');
                    return \Redirect::back()->withErrors(['Error', 'El usuario no se puedo actualizar, trate más tarde']);

                }
            }else{
                DB::rollBack();
                // return redirect('user.editMyGlobal')->with('mensaje', 'El usuario no se puedo actualizar, trate más tarde');
                return \Redirect::back()->withErrors(['Error', 'El usuario no se puedo actualizar, trate más tarde']);
            }               
        } catch (\Throwable $th) {
            DB::rollBack();
            // return redirect('user.editMyGlobal')->with('mensaje', 'El usuario no se puedo actualizar, trate más tarde');
            return \Redirect::back()->withErrors(['Error', 'El usuario no se puedo actualizar, trate más tarde']);
        }  
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
        if( $request->con == 1){
            if( $request->passwordnu != $request->password ){
                return \Redirect::back()->withErrors(['Error', 'Las contraseña no es igual']);
            }
    
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->employee_id != 0){
                $user->employee_id = $request->employee_id;
            }
            $user->password = bcrypt($request->password);
            $user->is_delete = 0;
            $user->updated_by = session()->get('user_id');
            $user->save();

        }else{
            
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->employee_id != 0){
                $user->employee_id = $request->employee_id;
            }
            $user->is_delete = 0;
            $user->updated_by = session()->get('user_id');
            $user->save();    
        }
        
        

        return redirect('user')->with('mensaje', 'Usuario actualizado con exito');
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
            $user = User::find($id);
            $user->fill($request->all());
            $user->is_delete = 1;
            $user->updated_by = session()->get('user_id');
            $user->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }

    public function activar(Request $request,$id){
        if ($request->ajax()) {
            $user = User::find($id);
            $user->fill($request->all());
            $user->is_delete = 0;
            $user->updated_by = session()->get('user_id');
            $user->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }    
    }

    public function change(){
        $id = session()->get('user_id');
        $data = User::find($id);
        return view('user.changePassword', compact('data'));   
    }

    public function updatePassword(Request $request, $id){
        
        if( $request->passwordnu != $request->password ){
            return \Redirect::back()->withErrors(['Error', 'Las contraseñas no son iguales']);
        }
        
        $data = User::find($id);
        $data->password = bcrypt($request->password);
        $data->updated_by = session()->get('user_id');
        $data->save();
        return redirect('/')->with('mensaje', 'Contraseña actualizado con exito');
    }

    public function storeCopyUser(Request $request){
        try {
            \DB::begintransaction();
            $dataUserPGU = \DB::table('prepayroll_groups_users')
                            ->where('head_user_id', $request->destinationUserId)
                            ->pluck('group_id');

            $dataUserGDU = \DB::table('group_dept_user')
                            ->where('user_id', $request->destinationUserId)
                            ->pluck('groupdept_id');

            $dataUserRol = \DB::table('user_rol')
                            ->where('user_id', $request->destinationUserId)
                            ->pluck('rol_id');
    
            $dataOriginPGU = \DB::table('prepayroll_groups_users')
                            ->where('head_user_id', $request->originUserId)
                            ->whereNotIn('group_id', $dataUserPGU)
                            ->get();

            $dataOriginGDU = \DB::table('group_dept_user')
                            ->where('user_id', $request->originUserId)
                            ->whereNotIn('groupdept_id', $dataUserGDU)
                            ->get();

            $dataOriginRol = \DB::table('user_rol')
                            ->where('user_id', $request->originUserId)
                            ->whereNotIn('rol_id', $dataUserRol)
                            ->get();
    
            foreach($dataOriginPGU as $oData){
                $dataPGU['group_id'] = $oData->group_id;
                $dataPGU['head_user_id'] = $request->destinationUserId;
                $dataPGU['user_by_id'] = \Auth::id();
    
                \DB::table('prepayroll_groups_users')
                    ->insert($dataPGU);
            }

            foreach($dataOriginGDU as $oData){
                $dataGDU['user_id'] = $request->destinationUserId;
                $dataGDU['groupdept_id'] = $oData->groupdept_id;
                $dataGDU['created_by'] = \Auth::id();
                $dataGDU['updated_by'] = \Auth::id();
                $dataGDU['is_delete'] = 0;
    
                \DB::table('group_dept_user')
                    ->insert($dataGDU);
            }

            $result = \DB::table('user_rol')
                        ->where('user_id', $request->destinationUserId)
                        ->first();
            
            foreach($dataOriginRol as $oData){
                $dataRol['state'] = 1;
                $dataRol['rol_id'] = $oData->rol_id;
                $dataRol['user_id'] = $request->destinationUserId;
                if(is_null($result)){
                    \DB::table('user_rol')
                        ->insert($dataRol);
                }else{
                    \DB::table('user_rol')
                        ->where('user_id', $dataRol['user_id'])
                        ->update(['rol_id' => $dataRol['rol_id']]);
                }
    
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al copiar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro copiado con exitó', 'icon' => 'success']);
    }

    
}
