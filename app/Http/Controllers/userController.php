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
                $datas = User::where('is_delete','0')->orderBy('id')->get();
                $datas->each(function($datas){
                    $datas->employee;
                });
                break;
            case 2:
                $datas = User::where('is_delete','1')->orderBy('id')->get();
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
}
