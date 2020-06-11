<?php namespace App\SUtils;

use Carbon\Carbon;
use DB;

class SDateUtils {

    /**
     * gets the date of the last day of month and year received
     *
     * @param  string $iIni
     * @param  integer $iYear
     * @param  string $iFin
     * @param  integer $iTypePay
     *
     * @return array de semanas o de quincenas segun sea el tipo solicitado
     */
    public static function getNumWeek($iIni = '', $iYear = 0, $iFin = '',$sTypePay = 0) {
        switch($sTypePay){
            case 1:
                $inicio = DB::table('week_cut')
                        ->where('is_delete','0')
                        ->where('ini','<=',$iIni)
                        ->where('fin','>=',$iIni)
                        ->where('year','=',$iYear)
                        ->select('num AS num')
                        ->get();
                $final = DB::table('week_cut')
                        ->where('is_delete','0')
                        ->where('ini','<=',$iFin)
                        ->where('fin','>=',$iFin)
                        ->where('year','=',$iYear)
                        ->select('num AS num')
                        ->get();
                $contadorInicial = $inicio[0]->num;
                $contadorFinal = $final[0]->num;
                $contadorAux = 0;
                for($contadorInicial ; $contadorFinal >= $contadorInicial ; $contadorInicial ++){
                    $semanas[$contadorAux] = $contadorInicial;
                    $contadorAux ++;
                }

                return $semanas;
            break;
            case 2:
                $quincenas = DB::table('hrs_prepay_cut')
                        ->where('is_delete','0')
                        ->whereBetween('dt_cut', [$iIni, $iFin])
                        ->where('year','=',$iYear)
                        ->select('num AS num', 'dt_cut AS cut')
                        ->get();
                $faltante = 0;
                $contadorAux = count($quincenas);
                $comparacion = $quincenas[$contadorAux-1]->cut;
                if($comparacion != $iFin){
                    $faltante = 1;
                }
                for($i = 0 ; $contadorAux >= $i ; $i ++){
                    $quincena[$i] = $quincenas[$i]->num;
                }
                if($faltante == 1){
                    $quincena[$contadorAux] = ($quincena[$contadorAux-1]->num) + 1;
                }
            break;

        }
    }
    /**
     * gets the date of the last day of month and year received
     *
     * @param  array $iPeriods
     * @param  integer $iYear
     * 
     *
     * @return array de semanas o de quincenas segun sea el tipo solicitado
     */
    public static function isProcessed($iPeriods = [] , $iYear = 0, $iTypePay){
        switch($iTypePay){
            case 1:
                for( $i = 0; count($iPeriods) > $i ; $i++ ){
                    $procesado = DB::table('period_processed')
                                ->join('week_cut','week_cut.id','=','period_processed.num_week')
                                ->where('is_delete','0')
                                ->where('year','=',$iYear)
                                ->where('num',$iPeriods[$i])
                                ->select('is_close AS close')
                                ->get();
                    if($procesado != null){
                        $pendientes[$i] = $iPeriods[$i];
                    }elseif($procesado[0]->close == 0){
                        $pendientes[$i] = $iPeriods[$i];
                    }    
                }
            break;
            case 2:
                for( $i = 0; count($iPeriods) > $i ; $i++ ){
                    $procesado = DB::table('period_processed')
                                ->join('hrs_prepay_cut','hrs_prepay_cut.id','=','period_processed.num_biweekly')
                                ->where('is_delete','0')
                                ->where('year','=',$iYear)
                                ->where('num',$iPeriods[$i])
                                ->select('is_close AS close')
                                ->get();
                    if($procesado != null){
                        $pendientes[$i] = $iPeriods[$i];
                    }elseif($procesado[0]->close == 0){
                        $pendientes[$i] = $iPeriods[$i];
                    }    
                }
            break;
        }

        return $pendientes;
        

    }
}