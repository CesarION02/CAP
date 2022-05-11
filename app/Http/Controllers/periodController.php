<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\SUtils\SInfoWithPolicy;
use App\SUtils\SDateUtils;

class periodController extends Controller
{
    public function index(Request $request)
    {
        $start_date = null;
        $end_date = null;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $now = $now->format('Y'); 
            $start_date = $now.'-01-01';
            $end_date = $now.'-12-31';
        }
        else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $year = explode("-", $start_date);
            $now = $year[0];

        }

        $weeks = DB::table('period_processed')
                        ->where('year','=',$now)
                        ->where('num_week','!=',null)
                        ->select('num_week as week')
                        ->get();
        $biweeks = DB::table('period_processed')
                        ->where('year','=',$now)
                        ->where('num_biweekly','!=',null)
                        ->select('num_biweekly as biweekly')
                        ->get();
                        
        if(count($weeks) > 0){
            for( $i = 0 ; count($weeks) > $i ; $i++ ){
                $aWeek[$i] = $weeks[$i]->week;
            }
            $dataw = DB::table('week_cut')
                            ->where('year','=',$now)
                            ->whereIn('num',$aWeek)
                            ->get();
        }
        if(count($biweeks) > 0){
            for( $i = 0 ; count($biweeks) > $i ; $i++ ){
                $aBi[$i] = $biweeks[$i]->biweekly;
            }
            $datab = DB::table('hrs_prepay_cut')
                            ->where('year','=',$now)
                            ->whereIn('num',$aBi)
                            ->get();
        }
        return view('period.index')
                        ->with('start_date', $start_date)
                        ->with('end_date', $end_date)
                        ->with('dataw',$dataw)
                        ->with('datab',$datab);
    }

    public function create()
    {
        return view('period.create');
    }

    public function store(Request $request)
    {
        $sStartDate = $request->start_date;
        $sEndDate = $request->end_date;
        $payWay = $request->way_pay;
        $year = explode("-", $sStartDate);
        $prueba = SInfoWithPolicy::preProcessInfo($sStartDate,$year[0],$sEndDate,$payWay);
        return redirect('periods')->with('mensaje', 'Periodo procesado con exito');
    }

    public function getCuts(Request $request)
    {
        $iYear = $request->year;
        
        $response = SDateUtils::getCutoffDatesOfYear($iYear);

        return json_encode($response);
    }
}
