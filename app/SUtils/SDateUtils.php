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
    public static function getNumWeek($iIni, $iYear, $iFin,$sTypePay) {
        switch($sTypePay){
            case 2:
                $inicio = DB::table('week_cut')
                        ->where('ini','<=',$iIni)
                        ->where('fin','>=',$iIni)
                        ->where('year','=',$iYear)
                        ->select('num AS num')
                        ->get();
                $final = DB::table('week_cut')
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
            case 1:
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
                for($i = 0 ; $contadorAux > $i ; $i ++){
                    $quincena[$i] = $quincenas[$i]->num;
                }
                if($faltante == 1){
                    $auxiliar = $quincenas[$contadorAux-1]->num;
                    $quincena[$contadorAux] = $auxiliar + 1;
                }

                $quincenas = DB::table('hrs_prepay_cut')
                ->where('is_delete','0')
                ->where('year','=',$iYear)
                ->whereIn('num',$quincena)
                ->select('id AS id', 'id AS id')
                ->get();

                for ($i = 0 ; count($quincenas) > $i ; $i++ ){
                    $numQuincena[$i] = $quincenas[$i]->id;
                }
                
                return $numQuincena;
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
            case 2:
                for( $i = 0; count($iPeriods) > $i ; $i++ ){
                    $procesado = DB::table('period_processed')
                                ->join('week_cut','week_cut.id','=','period_processed.num_week')
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
            case 1:
                for( $i = 0; count($iPeriods) > $i ; $i++ ){
                    $procesado = DB::table('period_processed')
                                ->join('hrs_prepay_cut','hrs_prepay_cut.id','=','period_processed.num_biweekly')
                                ->where('is_delete','0')
                                ->where('hrs_prepay_cut.id',$iPeriods[$i])
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

    public static function separateBiweekly($quincena){
            $final = DB::table('hrs_prepay_cut')
                        ->where('id','=',$quincena)
                        ->select('dt_cut AS cut', 'num AS num', 'year AS year')
                        ->get();
            if($final[0]->num > 1){
                $inicioAux = DB::table('hrs_prepay_cut')
                ->where('year', $final[0]->year)
                ->where('num', ($final[0]->num)-1)
                ->select('dt_cut AS cut')
                ->get(); 

                $inicio = Carbon::parse($inicioAux[0]->cut);
                $inicio->addDay();
            }else{
                $inicioAux = DB::table('hrs_prepay-cut')
                ->where('year',($final[0]->year)-1)
                ->orderBy('num','DESC')
                ->select('dt_cut AS cut')
                ->get();
                
                $inicio = Carbon::parse($inicioAux[0]->cut);
                $inicio->addDay();
            }

            $primeraSemana = DB::table('week_cut')
                        ->where('ini','<=',$inicio->format('Y-m-d'))
                        ->where('fin','>=',$inicio->format('Y-m-d'))
                        ->select('num AS num')
                        ->get();
            $ultimaSemana = DB::table('week_cut')
                        ->where('ini','<=',$final[0]->cut)
                        ->where('fin','>=',$final[0]->cut)
                        ->select('num AS num')
                        ->get();
            $contadorInicial = $primeraSemana[0]->num;
            $contadorFinal = $ultimaSemana[0]->num;
            $contadorAux = 0;
            for($contadorInicial ; $contadorFinal >= $contadorInicial ; $contadorInicial ++){
                $semanas[$contadorAux] = $contadorInicial;
                $contadorAux ++;
            }

            return $semanas;
        
    }
}

