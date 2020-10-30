<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class inicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   $config = \App\SUtils\SConfiguration::getConfigurations();

        $correcto = \App\Http\Controllers\SyncController::syncronizeWithERP($config->lastSyncDateTime);
        $rol = session()->get('rol_id');
        $datas = DB::table('home_menus_rol')
                        ->join('menu','menu.id','=','home_menus_rol.menu_id')
                        ->where('home_menus_rol.rol_id',$rol)
                        ->select('menu.name AS nombreMenu', 'menu.url AS url', 'home_menus_rol.icono AS icono')
                        ->orderBy('home_menus_rol.order')
                        ->get();
        if( $correcto != 0){
            return view('inicio')->with('datas',$datas)->with('mensaje', 'Sincronizado');
        }else{
            return view('inicio')->with('datas',$datas)->with('mensaje', 'No se pudo sincronizar');
        }
        
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
