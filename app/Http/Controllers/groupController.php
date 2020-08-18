<?php

namespace App\Http\Controllers;
use App\Models\groupworkshift;
use App\Models\groupworkshiftline;
use App\Models\workshift;

use DB;
use Illuminate\Http\Request;

class groupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = groupworkshift::where('is_delete','0')->get();
        return view('group.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $workshift = workshift::where('is_delete','0')->orderBy('name','ASC')->get();
        $numero = count($workshift);
        return view('group.create')->with('workshift',$workshift)->with('numero',$numero);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $contador = $request->contador;
        $numSeleccion = 0;
        $arrSeleccion = [];
        for($i = 1;$i <= $contador ; $i++){
            if(isset($_POST['check'.$i])){
                $arrSeleccion[$numSeleccion] = $_POST['check'.$i];
                $numSeleccion ++;
            }
        }

        $group_workshift = new groupworkshift();
        $group_workshift->name = $request->name;
        $group_workshift->is_delete = 0;
        $group_workshift->created_by = session()->get('user_id');
        $group_workshift->updated_by = session()->get('user_id');
        $group_workshift->save();

        for($j = 0 ; $numSeleccion > $j ; $j++){
            $group_workshift_line = new groupworkshiftline();
            $group_workshift_line->group_workshifts_id = $group_workshift->id;
            $group_workshift_line->workshifts_id = $arrSeleccion[$j];
            $group_workshift_line->created_by = session()->get('user_id');
            $group_workshift_line->updated_by = session()->get('user_id');
            $group_workshift_line->save();    
        }


        return redirect('group')->with('mensaje','Grupo fue creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
          
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $group = DB::table('group_workshifts_lines')
                    ->join('group_workshifts','group_workshifts_lines.group_workshifts_id','=','group_workshifts.id')
                    ->where('group_workshifts.id',$id)
                    ->select('group_workshifts_lines.workshifts_id AS workshift','group_workshifts.id AS id', 'group_workshifts.name AS name')
                    ->get();
        $workshift = workshift::where('is_delete','0')->orderBy('name','ASC')->get();
        return view('group.edit', compact('group'))->with('workshift',$workshift);
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
        groupworkshiftline::where('group_workshifts_id',$id)->delete();
        $contador = $request->contador;
        $numSeleccion = 0;
        $arrSeleccion = [];
        for($i = 1;$i <= $contador ; $i++){
            if(isset($_POST['check'.$i])){
                $arrSeleccion[$numSeleccion] = $_POST['check'.$i];
                $numSeleccion ++;
            }
        }

        for($j = 0 ; $numSeleccion > $j ; $j++){
            $group_workshift_line = new groupworkshiftline();
            $group_workshift_line->group_workshifts_id = $id;
            $group_workshift_line->workshifts_id = $arrSeleccion[$j];
            $group_workshift_line->updated_by = session()->get('user_id');
            $group_workshift_line->save();    
        }

        return redirect('group')->with('mensaje','Grupo fue edita con éxito');
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

    public function mostrar($id)
    {
        $group = DB::table('group_workshifts_lines')
                    ->join('group_workshifts','group_workshifts_lines.group_workshifts_id','=','group_workshifts.id')
                    ->join('workshifts','workshifts.id','=','group_workshifts_lines.workshifts_id')
                    ->where('group_workshifts.id',$id)
                    ->select('workshifts.name AS nameWorkshift', 'group_workshifts.name AS nameGroup', 'workshifts.entry AS entry','workshifts.departure AS departure')
                    ->get();            
        
        return view('group.mostrar', compact('group'));
    }
}
