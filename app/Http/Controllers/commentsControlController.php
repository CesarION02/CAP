<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;
use App\Models\commentsControl;

class commentsControlController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $commentsControl = commentsControl::where('is_delete',0)->get();

        return view('commentsControl.index', ['lComments' => $commentsControl]);
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
    public function update(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if($request->id == 'trueAll'){
                    $comments = commentsControl::where('is_delete',0)->update(['value' => 1]);
                } else if($request->id == 'falseAll'){
                    $comments = commentsControl::where('is_delete',0)->update(['value' => 0]);
                } else{
                    $comment = commentsControl::findOrFail($request->id);
                    $comment->value = $request->value;
                    $comment->update();
                }
            });
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al actualizar el registro',
                'icon' => 'error'
            ]);
        }

        return response()->json([
            'message' => 'Registro actualizado con exito',
            'icon' => 'succces'
        ]);
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
