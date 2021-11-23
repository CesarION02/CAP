<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\SUtils\SInfoWithPolicy;

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
        $oDate = Carbon::parse($request->dt_date);
        
        $weeks = [];
        $biweeks = [];
        $biweeksCal = [];

        /**
         * Cortes de semana
         */
        $weeksQ = DB::table('week_cut')
                    ->where(function ($q) use ($oDate) {
                        $q->whereYear('ini', $oDate->year)
                            ->orWhereYear('fin', $oDate->year);
                    })
                    ->select('year', 'ini', 'fin', 'num')
                    ->orderBy('ini', 'ASC')
                    ->get();

        foreach ($weeksQ as $qweek) {
            $cut = (object) [];

            $cut->dt_start = $qweek->ini;
            $cut->dt_end = $qweek->fin;
            $cut->number = $qweek->num;

            $weeks[] = $cut;
        }

        /**
         * Cortes de quincena
         */
        $biweeksQ = DB::table('hrs_prepay_cut')
                    ->whereYear('dt_cut', $oDate->year)
                    ->where('is_delete', 0)
                    ->select('year', 'dt_cut', 'num')
                    ->orderBy('dt_cut', 'ASC')
                    ->get();

        $dtCutPrev = Carbon::parse($biweeksQ[0]->dt_cut)->subDays(15);
        foreach ($biweeksQ as $biweek) {
            $cut = (object) [];

            $cut->dt_start = $dtCutPrev->addDays(1)->toDateString();
            $cut->dt_end = $biweek->dt_cut;
            $cut->number = $biweek->num;
            $dtCutPrev = Carbon::parse($biweek->dt_cut);

            $biweeks[] = $cut;
        }

        /**
         * Cortes de quincena calendario
         */
        $nextYear = Carbon::parse(($oDate->year + 1) . '-01-01');
        $start = Carbon::parse($oDate->year . '-01-01');
        $number = 1;
        do {
            $middle = (clone $start)->addDays(14);
            $last = (clone $middle)->endOfMonth();

            $cut = (object) [];

            $cut->dt_start = $start->toDateString();
            $cut->dt_end = $middle->toDateString();
            $cut->number = $number++;

            $biweeksCal[] = $cut;

            $cut = (object) [];

            $cut->dt_start = $middle->addDay()->toDateString();
            $cut->dt_end = $last->toDateString();
            $cut->number = $number++;

            $biweeksCal[] = $cut;

            $start->addMonth();
        } while ($start->lessThan($nextYear));

        $response = (object) [];
        $response->weeks = $weeks;
        $response->biweeks = $biweeks;
        $response->biweekscal = $biweeksCal;

        return json_encode($response);
    }
}
