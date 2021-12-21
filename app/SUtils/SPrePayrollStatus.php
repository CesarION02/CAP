<?php namespace App\SUtils;

use Carbon\Carbon;
use DB;
use App\Models\processed_data;
use App\Models\period_processed;

class SPrePayrollStatus {

    public static function getStatus($iPeriods = [] , $iYear = 0, $iTypePay,$force){
        $pendientes[0] = 0;
        switch($iTypePay){
            case 2:
                for( $i = 0; count($iPeriods) > $i ; $i++ ){
                    $procesado = DB::table('prepayroll_control')
                                ->join('week_cut','week_cut.id','=','period_processed.num_week')
                                ->where('week_cut.year','=',$iYear)
                                ->where('week_cut.num',$iPeriods[$i])
                                ->where('prepayroll_control.status',2)
                                ->get();
                    if(empty($procesado[0]) || $force == 1){
                        $pendientes[$i] = $iPeriods[$i];
                    }    
                }
                if($pendientes[0] != 0){
                    $semanasEliminar = DB::table('week_cut')
                                ->whereIn('num',$pendientes)
                                ->where('year',$iYear)
                                ->select('id AS id')
                                ->get();
                    for($i = 0 ; count($semanasEliminar) > $i ; $i++ ){
                        $res = period_processed::where('num_week',$semanasEliminar[$i]->id)->delete();
                        $borrar = processed_data::where('week',$semanasEliminar[$i]->id)->where('year',$iYear)->delete();
                    }
                }
            break;
            case 1:
                for( $i = 0; count($iPeriods) > $i ; $i++ ){
                    $procesado = DB::table('period_processed')
                                ->join('hrs_prepay_cut','hrs_prepay_cut.id','=','period_processed.num_biweekly')
                                ->where('is_delete','0')
                                ->where('hrs_prepay_cut.id',$iPeriods[$i])
                                ->select('period_processed.updated_at AS update', 'dt_cut AS cut')
                                ->get();
                    if(empty($procesado[0])){
                        $pendientes[$i] = $iPeriods[$i];
                    }elseif($procesado[0]->cut >= $procesado[0]->update){
                        $pendientes[$i] = $iPeriods[$i];
                    }    
                }
                if($pendientes[0] != 0){
                $semanasEliminar = DB::table('hrs_prepay_cut')
                                ->whereIn('num',$pendientes)
                                ->where('year',$iYear)
                                ->select('id AS id')
                                ->get();
                for($i = 0 ; count($semanasEliminar) > $i ; $i++ ){
                    $res = period_processed::where('num_biweekly',$semanasEliminar[$i]->id)->delete();
                    $borrar = processed_data::where('biweek',$semanasEliminar[$i]->id)->where('year',$iYear)->delete();
                }
                }
            break;
        }

        return $pendientes;    
    }
}