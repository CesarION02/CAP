<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin\rol;
use App\Models\employees;
use App\Models\User;

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

        switch ($iFilter) {
            case 1:
                $datas = User::where('is_delete','0')->orderBy('name')->get();
                $datas->each(function($datas){
                    $datas->employee;
                });
                break;
            case 2:
                $datas = User::where('is_delete','1')->orderBy('name')->get();
                $datas->each(function($datas){
                    $datas->employee;
                });
                break;
            
            default:
                $datas = User::orderBy('id')->get();
                $datas->each(function($datas){
                    $datas->employee;
                });
                break;
        }
        
        return view('user.index', compact('datas'))->with('iFilter',$iFilter);
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
