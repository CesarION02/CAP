<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;
use App\Models\Comments;

class commentsController extends Controller
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
                $comments = comments::where('is_delete', 0)->get();
                break;
            case 2:
                $comments = comments::where('is_delete', 1)->get();
                break;
            
            default:
                $comments = comments::get();
                break;
        }

        $comments->each(function ($data) {
            $data->userCreated;
            $data->userEdited;
        });

        return view('comments.index', ['lComments' => $comments, 'iFilter' => $iFilter]);
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
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return response()->json([
                    'success' => false,
                    'message' => 'Debe ingresar un comentario',
                    'icon' => 'error',
                    'oComment' => null
                ]);
            }
        }
        $comment = null;
        try {
            $comment = Comments::create([
                'comment' => $request->comment,
                'is_delete' => 0,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro',
                'icon' => 'error'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Se guardó el registro con éxito',
            'icon' => 'success',
            'oComment' => $comment
        ]);
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
        foreach($request->all() as $elem){
            if(is_null($elem)){
                return response()->json([
                    'success' => false,
                    'message' => 'Debe ingresar un comentario',
                    'icon' => 'error',
                    'oComment' => null
                ]);
            }
        }
        $comment = null;
        try {
            DB::transaction( function () use($request, $id, $comment){
                $comment = Comments::findorFail($id);
                $comment->comment = $request->comment;
                $comment->is_delete = 0;
                $comment->updated_by = auth()->user()->id;
                $comment->update();
            });
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro',
                'icon' => 'error'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó el registro con éxito',
            'icon' => 'success',
            'oComment' => $comment
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
        try {
            DB::transaction( function () use($id){
                $comment = Comments::findorFail($id);
                $comment->is_delete = 1;
                $comment->updated_by = auth()->user()->id;
                $comment->update();
            });
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al eliminar el registro',
                'icon' => 'error'
            ]);
        }

        return response()->json([
            'message' => 'Se eliminó el registro con éxito',
            'icon' => 'success'
        ]);
    }

    public function recover($id)
    {
        try {
            DB::transaction( function () use($id){
                $comment = Comments::findorFail($id);
                $comment->is_delete = 0;
                $comment->updated_by = auth()->user()->id;
                $comment->update();
            });
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al recuperar el registro',
                'icon' => 'error'
            ]);
        }

        return response()->json([
            'message' => 'Se recuperó el registro con éxito',
            'icon' => 'success'
        ]);
    }
}
