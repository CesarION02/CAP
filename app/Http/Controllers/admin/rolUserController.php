<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use App\Models\User;
use App\Models\admin\rol;
use App\Models\admin\roluser;

class rolUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = DB::table('users')
                        ->join('user_rol','user_rol.user_id','=','users.id')
                        ->join('rol','rol.id','=','user_rol.rol_id')
                        ->where('state',1)
                        ->select('rol.name AS nameRol','users.name AS nameUser','user_rol.id AS id')
                        ->get();

        return view('admin.roluser.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::where('is_delete',0)->pluck('id','name');
        $rols = rol::orderBy('id')->pluck('id','name');
        
        return view('admin.roluser.create')->with('users',$users)->with('rols',$rols);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $roluser = new roluser();
        $roluser->user_id = $request->usuario_id;
        $roluser->rol_id = $request->rol_id;
        $roluser->state = 1;
        $roluser->save();

        return redirect('admin/rol-user')->with('mensaje','Rol usuario fue creado con exito');
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
        //
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
        //
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
            $roluser = roluser::find($id);
            $roluser->fill($request->all());
            $roluser->state = 0;
            $roluser->save();
            return response()->json(['mensaje' => 'ok']);
        } else {
            abort(404);
        }
    }
}
