<?php namespace App\SUtils;

use Carbon\Carbon;
use DB;
use App\Models\processed_data;
use App\Models\period_processed;

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
                if( $inicio != null && $final != null ){        
                    $contadorInicial = $inicio[0]->num;
                    $contadorFinal = $final[0]->num;
                    $contadorAux = 0;
                    for($contadorInicial ; $contadorFinal >= $contadorInicial ; $contadorInicial ++){
                        $semanas[$contadorAux] = $contadorInicial;
                        $contadorAux ++;
                    }
                }else {
                    $semanas[0] = 0;
                }

                return $semanas;
            break;
            case 1:
                $quincenaIni = DB::table('hrs_prepay_cut')
                        ->where('is_delete','0')
                        //->whereBetween('dt_cut', [$iIni, $iFin])
                        ->where('dt_cut','>=',$iIni)
                        ->where('year','=',$iYear)
                        ->orderBy('dt_cut','ASC')
                        ->select('num AS num', 'dt_cut AS cut')
                        ->get();
                $quincenaFin = DB::table('hrs_prepay_cut')
                        ->where('is_delete','0')
                        ->where('dt_cut','>=',$iFin)
                        ->where('year','=',$iYear)
                        ->select('num AS num', 'dt_cut AS cut')
                        ->get();
                $inicioquincena = $quincenaIni[0]->num;
                $finquincena = $quincenaFin[0]->num;
                $faltante = 0;
                //$contadorAux = count($quincenas);
                //$comparacion = $quincenas[$contadorAux-1]->cut;
                //if($comparacion != $iFin){
                    //$faltante = 1;
                //}
                //for($i = 0 ; $contadorAux > $i ; $i ++){
                    //$quincena[$i] = $quincenas[$i]->num;
                //}
                if($finquincena > $inicioquincena){
                    for($i = 0 ; $finquincena >= $inicioquincena ; $i++){
                        $quincena[$i] = $inicioquincena;
                        $inicioquincena++;
                    }
                }else{
                    $quincena[0] = $inicioquincena;
                }
                //if($faltante == 1){
                    //$auxiliar = $quincenas[$contadorAux-1]->num;
                    //$quincena[$contadorAux] = $auxiliar + 1;
                //}

                $quincenas = DB::table('hrs_prepay_cut')
                ->where('is_delete','0')
                ->where('year','=',$iYear)
                ->whereIn('num',$quincena)
                ->select('id AS id')
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
        $pendientes[0] = 0;
        switch($iTypePay){
            case 2:
                for( $i = 0; count($iPeriods) > $i ; $i++ ){
                    $procesado = DB::table('period_processed')
                                ->join('week_cut','week_cut.id','=','period_processed.num_week')
                                ->where('week_cut.year','=',$iYear)
                                ->where('week_cut.num',$iPeriods[$i])
                                ->select('period_processed.updated_at AS update', 'fin AS fin')
                                ->get();
                    if(empty($procesado[0])){
                        $pendientes[$i] = $iPeriods[$i];
                    }elseif($procesado[0]->update <= $procesado[0]->fin){
                        $pendientes[$i] = $iPeriods[$i];
                    }    
                }
                if($pendientes[0] != 0){
                $semanasEliminar = DB::table('week_cut')
                                ->whereIn('num',$pendientes)
                                //->where('year',$iYear)
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

    public static function separateBiweekly($quincena){
            $final = DB::table('hrs_prepay_cut')
                        ->where('id','=',$quincena)
                        ->select('dt_cut AS cut', 'num AS num', 'year AS year')
                        ->get();
            if($final[0]->num > 1){
                $inicioAux = DB::table('hrs_prepay_cut')
                //->where('year', $final[0]->year)
                ->where('num', ($final[0]->num)-1)
                ->where('year',$final[0]->year)
                ->select('dt_cut AS cut')
                ->get(); 

                $inicio = Carbon::parse($inicioAux[0]->cut);
                $inicio->addDay();
            }else{
                $inicioAux = DB::table('hrs_prepay_cut')
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
                        ->select('id AS num')
                        ->get();
            $ultimaSemana = DB::table('week_cut')
                        ->where('ini','<=',$final[0]->cut)
                        ->where('fin','>=',$final[0]->cut)
                        ->select('id AS num')
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

    /**
     * Undocumented function
     *
     * @param string $dtDate
     * @param integer $payTypeId
     * @return array [número de quincena/semana, año]
     */
    public static function getNumberOfDate($dtDate, $payTypeId)
    {
        /**
         * Determinar el número de semana o quincena en base a la fecha recibida
         */
        $oDate = Carbon::parse($dtDate);
        $oNumber = null;
        if ($payTypeId == \SCons::PAY_W_Q) {
            $quin = \DB::table('hrs_prepay_cut AS hpc')
                        ->where('dt_cut', '>=', $dtDate)
                        // ->where('year', $oDate->year)
                        ->where('is_delete', false)
                        ->orderBy('dt_cut', 'ASC')
                        ->first();

            if (is_null($quin)) {
                throw new \Exception("No existe un corte de quincena para la fecha ". $dtDate . ", contacte al encargado de nóminas.", 501);
            }

            $oNumber = $quin;
        }
        else {
            $week = \DB::table('week_cut AS wc')
                        ->whereRaw("'".$oDate->toDateString()."' BETWEEN ini AND fin")
                        // ->where('year', $oDate->year)
                        ->first();

            if (is_null($week)) {
                throw new \Exception("No existe un corte de semana para la fecha ". $dtDate . ", contacte al encargado de nóminas.", 501);
            }

            $oNumber = $week;
        }

        return [$oNumber->num, $oNumber->year];
    }

    /**
     * Retorna un array con las fechas de inicio y fin de la semana o quincena
     *
     * @param integer $num
     * @param integer $year
     * @param integer $payTypeId
     * 
     * @return array
     */
    public static function getDatesOfPayrollNumber($num, $year, $payTypeId)
    {
        if ($payTypeId == \SCons::PAY_W_Q) {
            if ($num == 1) {
                $yearIni = $year - 1;
                $numIni = \DB::table('hrs_prepay_cut AS hpc')
                            ->where([['year', $year - 1], ['is_delete', false]])
                            ->orderBy('num', 'desc')
                            ->value('num');
            }
            else {
                $yearIni = $year;
                $numIni = $num - 1;
            }

            $ini = \DB::table('hrs_prepay_cut AS hpc')
                        ->where([['year', $yearIni], ['num', $numIni], ['is_delete', false]])
                        ->value('dt_cut');

            $fin = \DB::table('hrs_prepay_cut AS hpc')
                        ->where([['year', $year], ['num', $num], ['is_delete', false]])
                        ->value('dt_cut');

            $ini = date('Y-m-d', (strtotime('+1 day', strtotime($ini))));

            $data = [$ini, $fin];
        }
        else {
            $week = \DB::table('week_cut AS wc')
                        ->where([['year', $year], ['num', $num]])
                        ->select('ini', 'fin')
                        ->first();

            $data = [$week->ini, $week->fin];
        }

        return $data;
    }

    public static function getCutoffDatesOfYear($iYear)
    {
        $weeks = [];
        $biweeks = [];
        $biweeksCal = [];

        /**
         * Cortes de semana
         */
        $weeksQ = DB::table('week_cut')
                    ->where(function ($q) use ($iYear) {
                        $q->whereYear('ini', $iYear)
                            ->orWhereYear('fin', $iYear);
                    })
                    ->select('year', 'ini', 'fin', 'num')
                    ->orderBy('ini', 'ASC')
                    ->get();

        foreach ($weeksQ as $qweek) {
            $cut = (object) [];

            $cut->dt_start = $qweek->ini;
            $cut->dt_end = $qweek->fin;
            $cut->number = $qweek->num;
            $cut->year = $qweek->year;

            $weeks[] = $cut;
        }

        /**
         * Cortes de quincena
         */
        $biweeksQ = DB::table('hrs_prepay_cut')
                    ->where('year', $iYear)
                    ->where('is_delete', 0)
                    ->select('year', 'dt_cut', 'num')
                    ->orderBy('dt_cut', 'ASC')
                    ->get();

        if (count($biweeksQ) > 0) {
            $dtCutPrev = Carbon::parse($biweeksQ[0]->dt_cut)->subDays(15);
            foreach ($biweeksQ as $biweek) {
                $cut = (object) [];

                $cut->dt_start = $dtCutPrev->addDays(1)->toDateString();
                $cut->dt_end = $biweek->dt_cut;
                $cut->number = $biweek->num;
                $cut->year = $biweek->year;
                $dtCutPrev = Carbon::parse($biweek->dt_cut);

                $biweeks[] = $cut;
            }
        }

        /**
         * Cortes de quincena calendario
         */
        $nextYear = Carbon::parse(($iYear + 1) . '-01-01');
        $start = Carbon::parse($iYear . '-01-01');
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

        return $response;
    }

    public static function getInfoDates($ini, $fin,$typePay){
        switch($typePay){
            case 2:
                $dates = DB::table('week_cut')
                            ->whereBetween('ini', [$ini,$fin])
                            ->OrwhereBetween('fin', [$ini,$fin])
                            ->get();
            break;
            case 1:
                $dates = DB::table('hrs_prepay_cut')
                            ->whereBetween('dt_cut', [$ini,$fin])
                            ->get();
                if( $fin > $dates[count($dates)-1]->dt_cut ){
                    $dateAux = DB::table('hrs_prepay_cut')
                            ->where('dt_cut', '>', $fin )
                            ->first();
                            
                    // array_push( $dates, (array)$dateAux[0]);    
                    $dates = $dates->push($dateAux);
                }
            break;
        } 
        return $dates;   
    }
}

