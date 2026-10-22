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
                $comments = DB::table('comments as c')
                            ->select(
                                'c.id',
                                'c.comment',
                                DB::raw("GROUP_CONCAT(DISTINCT at.type_name ORDER BY pac.sort_number SEPARATOR ', ') as adjust_types"),
                                'u1.name as created_by_user',
                                'u2.name as updated_by_user',
                                'c.is_delete as is_delete'
                            )
                            ->leftJoin('prepayroll_adjusts_comments as pac', 'c.id', '=', 'pac.comment_id')
                            ->leftJoin('prepayroll_adjusts_types as at', 'pac.adjust_type_id', '=', 'at.id')
                            ->Join('users as u1', 'c.created_by', '=', 'u1.id')
                            ->Join('users as u2', 'c.updated_by', '=', 'u2.id')
                            ->where('c.is_delete', 0)
                            ->groupBy('c.id', 'c.comment', 'u1.username', 'u2.username')
                            ->orderBy('c.id')
                            ->get();
                break;
            case 2:
                $comments = DB::table('comments as c')
                            ->select(
                                'c.id',
                                'c.comment',
                                DB::raw("GROUP_CONCAT(DISTINCT at.type_name ORDER BY pac.sort_number SEPARATOR ', ') as adjust_types"),
                                'u1.name as created_by_user',
                                'u2.name as updated_by_user',
                                'c.is_delete as is_delete'
                            )
                            ->leftJoin('prepayroll_adjusts_comments as pac', 'c.id', '=', 'pac.comment_id')
                            ->leftJoin('prepayroll_adjusts_types as at', 'pac.adjust_type_id', '=', 'at.id')
                            ->Join('users as u1', 'c.created_by', '=', 'u1.id')
                            ->Join('users as u2', 'c.updated_by', '=', 'u2.id')
                            ->where('c.is_delete', 1)
                            ->groupBy('c.id', 'c.comment', 'u1.username', 'u2.username')
                            ->orderBy('c.id')
                            ->get();
                break;
                
            default:
            $comments = DB::table('comments as c')
                            ->select(
                                'c.id',
                                'c.comment',
                                DB::raw("GROUP_CONCAT(DISTINCT at.type_name ORDER BY pac.sort_number SEPARATOR ', ') as adjust_types"),
                                'u1.name as created_by_user',
                                'u2.name as updated_by_user',
                                'c.is_delete as is_delete'
                            )
                            ->leftJoin('prepayroll_adjusts_comments as pac', 'c.id', '=', 'pac.comment_id')
                            ->leftJoin('prepayroll_adjusts_types as at', 'pac.adjust_type_id', '=', 'at.id')
                            ->Join('users as u1', 'c.created_by', '=', 'u1.id')
                            ->Join('users as u2', 'c.updated_by', '=', 'u2.id')
                            ->groupBy('c.id', 'c.comment', 'u1.username', 'u2.username')
                            ->orderBy('c.id')
                            ->get();
                break;
        }
        foreach ($comments as $comment) {
            $adjustTypeIds = DB::table('prepayroll_adjusts_comments')
                                ->where('comment_id', $comment->id)
                                ->pluck('adjust_type_id');
        
            $comment->adjust_type_ids = $adjustTypeIds;
        }
        $adjustTypes = DB::table('prepayroll_adjusts_types')->select('id', 'type_name')->get();

        return view('comments.index', [
            'lComments' => $comments, 
            'iFilter' => $iFilter,
            'adjustTypes' => $adjustTypes
        ]);
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

            $inserts = [];
            $sort = 1;

            foreach ($request->types as $typeId) {
                $inserts[] = [
                    'sort_number' => $sort++,
                    'comment_id' => $comment->id,
                    'adjust_type_id' => $typeId,
                ];
            }

            DB::table('prepayroll_adjusts_comments')->insert($inserts);
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

                // 2. Eliminar los ajustes anteriores
                DB::table('prepayroll_adjusts_comments')
                    ->where('comment_id', $comment->id)
                    ->delete();

                // 3. Insertar los nuevos ajustes
                $inserts = [];
                $sort = 1;

                foreach ($request->types as $typeId) {
                    $inserts[] = [
                        'sort_number' => $sort++,
                        'comment_id' => $comment->id,
                        'adjust_type_id' => $typeId,
                    ];
                }

                DB::table('prepayroll_adjusts_comments')->insert($inserts);
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
