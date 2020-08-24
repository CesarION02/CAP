<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin\rol;
use App\User;

class userController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $iFilter = $request->filter_acts == null ? 1 : $request->filter_acts;

        switch ($iFilter) {
            case 1:
                $datas = User::where('is_delete','0')->orderBy('id')->get();
                $datas->each(function($datas){
                    $datas->area;
                });
                break;
            case 2:
                $datas = User::where('is_delete','1')->orderBy('id')->get();
                $datas->each(function($datas){
                    $datas->area;
                });
                break;
            
            default:
                $datas = User::orderBy('id')->get();
                $datas->each(function($datas){
                    $datas->area;
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
        //$datas = rol::orderBy('id','ASC')->pluck('id','name');
        return view('user.create', compact('datas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
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
        return view('user.edit', compact('data'));
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
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->is_delete = 0;
        $user->updated_by = session()->get('user_id');
        $user->save();

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
        $data = User::find($id);
        $data->password = bcrypt($request->password);
        $data->updated_by = session()->get('user_id');
        $data->save();
        return redirect('/')->with('mensaje', 'Contraseña actualizado con exito');
    }
}
