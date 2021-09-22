<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\incident;
use App\SUtils\SHolidayWork;
use App\Models\holidayworked;
use DB;

class daygrantedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request )
    {
        $iFilter = $request->ifilter == 0 ? 1 : $request->ifilter;

        switch ($iFilter) {
            case 2:
                $datas = DB::table('incidents')
                            ->join('employees','employees.id','=','incidents.employee_id')
                            ->join('holiday_worked','holiday_worked.id','=','incidents.holiday_worked_id')
                            ->join('holidays','holidays.id','=','holiday_worked.holiday_id')
                            ->orderBy('incidents.start_date')
                            ->where('incidents.is_delete','0')
                            ->where('incidents.start_date','0000-00-00')
                            ->where('incidents.type_incidents_id','17')
                            ->select('employees.name AS nameEmp','holidays.name AS nameholi','incidents.start_date AS date','incidents.comment AS comentarios','incidents.id AS id')
                            ->get();
                break;
            case 1:
                $datas = DB::table('incidents')
                            ->join('employees','employees.id','=','incidents.employee_id')
                            ->join('holiday_worked','holiday_worked.id','=','incidents.holiday_worked_id')
                            ->join('holidays','holidays.id','=','holiday_worked.holiday_id')
                            ->orderBy('incidents.start_date')
                            ->where('incidents.is_delete','0')
                            ->where('incidents.start_date','!=','0000-00-00')
                            ->where('incidents.type_incidents_id','17')
                            ->select('employees.name AS nameEmp','holidays.name AS nameholi','incidents.start_date AS date','incidents.comment AS comentarios','incidents.id AS id')
                            ->get();
                break;
            case 3:
                $datas = DB::table('incidents')
                            ->join('employees','employees.id','=','incidents.employee_id')
                            ->join('holiday_worked','holiday_worked.id','=','incidents.holiday_worked_id')
                            ->join('holidays','holidays.id','=','holiday_worked.holiday_id')
                            ->orderBy('incidents.start_date')
                            ->where('incidents.is_delete','0')
                            ->where('incidents.type_incidents_id','17')
                            ->select('employees.name AS nameEmp','holidays.name AS nameholi','incidents.start_date AS date','incidents.comment AS comentarios','incidents.id AS id')
                            ->get();
                break;
        }

        return view('day_granted.index')->with('datas', $datas)->with('iFilter',$iFilter);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
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
        $datas = DB::table('incidents')
                        ->join('employees','employees.id','=','incidents.employee_id')
                        ->join('holiday_worked','holiday_worked.id','=','incidents.holiday_worked_id')
                        ->join('holidays','holidays.id','=','holiday_worked.holiday_id')
                        ->orderBy('incidents.start_date')
                        ->where('incidents.is_delete','0')
                        ->where('incidents.type_incidents_id','17')
                        ->where('incidents.id','=',$id)
                        ->select('employees.name AS nameEmp','holidays.name AS nameholi','incidents.start_date AS date','incidents.comment AS comentarios','incidents.id AS id')
                        ->get(); 
        
        
        return view('day_granted.edit')->with('datas', $datas);
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
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $nuevo_otorgamiento = 0;
        $incident = incident::findOrFail($id);

        if ( $incident->start_date == '0000-00-00' ) {
            $nuevo_otorgamiento = 1;
        }

        $incident->start_date = $request->start_date;
        $incident->end_date = $request->start_date;
        $incident->comment = $request->comment;

        $incident->save();

        $holidayworked = holidayworked::findOrFail($incident->holiday_worked_id);
        if( $config->days_granted > $holidayworked->number_assignments && $nuevo_otorgamiento = 1 ){

            $holidayworked->number_assignments = $holidayworked->number_assignments + 1;
            $holidayworked->save();
        }
            

        return redirect('daygranted')->with('mensaje', 'Día otorgado con exito');
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

    public function holidayworked() 
    {
        $datas = DB::table('holiday_worked')
                    ->join('employees','employees.id','=','holiday_worked.employee_id')
                    ->join('holidays','holidays.id','=','holiday_worked.holiday_id')
                    ->orderBy('holidays.fecha')
                    ->where('holiday_worked.is_delete','0')
                    ->select('employees.name AS nameEmp','holidays.name AS nameholi','holidays.fecha AS date','holiday_worked.number_assignments AS assignments')
                    ->get();
        
        return view('day_granted.holidayworked')->with('datas', $datas);    
    }

    public function fechasholidayworked() 
    {
        return view('day_granted.fechasholidayworked');
    }

    public function generarholiday(Request $request){
        SHolidayWork::holidayworked($request->start_date,$request->end_date);

        return redirect()->back()->with('mensaje', 'Días festivos trabajados ingresados');
    }

    public function cancelholiday($id){

    }
}
