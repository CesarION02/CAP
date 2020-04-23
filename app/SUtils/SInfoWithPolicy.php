<?php namespace App\SUtils;
use DataTime;
use Carbon\Carbon;

class SInfoWithPolicy{
/**
     * var_dump(Carbon::SUNDAY);     // int(0)
     * var_dump(Carbon::MONDAY);     // int(1)
     * var_dump(Carbon::TUESDAY);    // int(2)
     * var_dump(Carbon::WEDNESDAY);  // int(3)
     * var_dump(Carbon::THURSDAY);   // int(4)
     * var_dump(Carbon::FRIDAY);     // int(5)
     * var_dump(Carbon::SATURDAY);   // int(6)
     */

     /**
      * Realiza el proceso de empatar checadas vs horarios programados y regresa una
      * lista de SRegistryRow con los datos correspondientes
      *
      * @param string $sStartDate
      * @param string $sEndDate
      * @param int $payWay [ 1: QUINCENA, 2: SEMANA, 0: TODOS]
      * @param array $lEmployees arreglo de ids de empleados

      * @return [SRegistryRow] (array)
      */
      public static function processInfo($sStartDate, $sEndDate, $payWay, $lEmployees,$typeInfo)
      {
        $lRows = SDelayReportUtils::processReport($sStartDate, $sEndDate, $payWay, \SCons::REP_HR_EX, $lEmployees);
        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sStartDate);
        $contadorRenglones = 0;
        $config = \App\SUtils\SConfiguration::getConfigurations();
       // Número de días que se incluirán
       // $diff = $dateS->diff($dateE);

        switch($config->policy){
            case 1:
                $hrsBiweekly = 18;
                $hrsWeekly = 9;
                $horasCompletas = 0;
                $horasMedias = 0;
                $sumaHoras = 0;
                $limitHours = 0;
                $HalfPendient = 0;    //0-> no se tienen medias horas pendientes 1-> se tiene media hora pendiente
                //constantes
                $compEmpleado = 0;
                $cambioEmpleado = 0;
                $initialLimitHalf = 20;
                $finalLimitHalf = 30;
                $initialLimitHour = 50;
                $finalLimitHour = 60;
                if($payWay == 1 ){
                    $groupDay = 14;
                    $limitHours = $hrsBiweekly;
                }else if($payWay == 2){
                    $groupDay = 6;
                    $limitHours = $hrsWeekly;
                }

                    for( $j = 0 ; count($lRows) > $j ; $j ){
                        if($auxFin < $dateE){
                            $auxFin->addDays($groupDay);
                        }else{$j++;}
                        while( $auxFin >= $auxIni ){
                            if($lRows[$j]->inDate != null){
                                $auxComparacion = Carbon::parse($lRows[$j]->inDate);
                            }else{
                                $auxComparacion = Carbon::parse($sStartDate);
                                $auxComparacion->subDay();
                            }
                            
                            if($compEmpleado != $lRows[$j]->idEmployee){
                                if($compEmpleado != 0){
                                    $cambioEmpleado = 1;
                                }
                                $compEmpleado = $lRows[$j]->idEmployee;
                            }
                            //Si la fecha es la correcta o no hay registro para ese día
                            if($auxIni == $auxComparacion){
                                $contadorRenglones++;
                            // si tiene más de una hora de tiempo extra
                                if( $lRows[$j]->delayMins > 60 ){
                                    $horasCompletas = intdiv($lRows[$j]->delayMins,60);
                                    $horasMedias = $lRows[$j]->delayMins % 60;
                                    $auxHorasCompletas = 0;
                                    $auxHorasMedias = 0;
                                    if( $horasMedias >= $initialLimitHour && $finalLimitHour >= $horasMedias ){
                                        $auxHorasCompletas = 1; 
                                    }else if( $horasMedias >= $initialLimitHalf && $finalLimitHalf >= $horasMedias ){
                                        $auxHorasMedias = 1;
                                    }
                                    if( $HalfPendient == 1 && $auxHorasMedias == 1){
                                        $auxHorasCompletas = $auxHorasCompletas + 1 ;
                                    }else if( $HalfPendient == 0 && $auxHorasMedias == 1){
                                        $HalfPendient = 1;
                                    }
                                    //si la suma de horas no paso el limite
                                    if($sumaHoras < $limitHours){
                                        $sumaHorasAuxiliar = $sumaHoras + $horasCompletas + $auxHorasCompletas;
                                        //checar si se pasa al sumar las horas del dia
                                        if($sumaHorasAuxiliar > $limitHours){
                                            $horasFueraLimite = $sumaHorasAuxiliar - $limitHours;
                                            $horasDentroLimite = $horasCompletas + $auxHorasCompletas - $horasFueraLimite;
                                            $sumaHoras = 9;
                                            $lRows[$j]->extraDoubleMins = $horasDentroLimite*60;
                                            $lRows[$j]->extraTripleMins = $horasFueraLimite*60;
                                        //si no se pasa continua normal
                                        }else{
                                            $sumaHoras = $sumaHorasAuxiliar;
                                            $lRows[$j]->extraDoubleMins = ($horasCompletas + $auxHorasCompletas) * 60;
                                            $lRows[$j]->extraTripleMins = 0;
                                        }    
                                    //si la suma de horas paso el limite
                                    }else{
                                        $horasCompletas = ($horasCompletas + $auxHorasCompletas)*60;
                                        $lRows[$j]->extraDoubleMins = 0;
                                        $lRows[$j]->extraTripleMins = $horasCompletas;     
                                    }  
                                //si tiene menos de una hora de tiempo extra
                                }else{
                                    // si supera los limites para ser una hora
                                    if( $lRows[$j]->delayMins >= $initialLimitHour && $finalLimitHour >= $lRows[$j]->delayMins){
                                        //si la suma de horas aun no pasa el limite
                                        if($sumaHoras < $limitHours){
                                            $lRows[$j]->extraDoubleMins = 60;
                                            $lRows[$j]->extraTripleMins = 0; 
                                            $sumaHoras = $sumaHoras + 1;
                                        //si la suma de horas paso el limite
                                        }else{
                                            $lRows[$j]->extraDoubleMins = 0;
                                            $lRows[$j]->extraTripleMins = 60; 
                                        }
                                    //si supera los limites para ser media hora
                                    }else if($lRows[$j]->delayMins >= $initialLimitHalf && $finalLimitHalf >= $lRows[$j]->delayMins){
                                        //se viene arrastrando una media hora
                                        if( $HalfPendient == 1 ){
                                            //si la suma de horas esta por debajo del limite
                                            if( $sumaHoras < $limitHours ){
                                                $lRows[$j]->extraDoubleMins = 60;
                                                $lRows[$j]->extraTripleMins = 0; 
                                                $sumaHoras = $sumaHoras + 1;  
                                                $HalfPendient = 0; 
                                            //si la suma de horas es mayor al limite
                                            }else{
                                                $lRows[$j]->extraDoubleMins = 0;
                                                $lRows[$j]->extraTripleMins = 60; 
                                                $HalfPendient = 0;
                                            }
                                        //Si no se tenia media hora
                                        }else{
                                            $HalfPendient = 1;
                                            $lRows[$j]->extraDoubleMins = 0;
                                            $lRows[$j]->extraTripleMins = 0;
                                        }
                                    //no alcanza los limites para ser hora o media hora extra
                                    }else{
                                        $lRows[$j]->extraDoubleMins = 0;
                                        $lRows[$j]->extraTripleMins = 0;
                                    }
                                }
                                $j++;
                                if($j > count($lRows)){
                                    $auxIni = Carbon::parse($sEndDate);
                                    $auxIni->addDay(); 
                                }
                                $auxIni->addDay();
                            }else if($auxIni > $auxComparacion){
                                if($cambioEmpleado == 1){
                                    $auxIni = Carbon::parse($sEndDate);
                                    $auxIni->addDay();
                                }else{
                                    $j++;
                                    if($j > count($lRows)){
                                        $auxIni = Carbon::parse($sEndDate);
                                        $auxIni->addDay(); 
                                    }
                                }    
                            }else{
                                $auxIni->addDay();
                            }
                        
                            //si es el ultimo dia del grupo y se tiene una media hora sobrante 
                            if($auxIni > $auxFin && $HalfPendient == 1){
                                //si la suma de horas es menor al limite -> media hora Double
                                if($limitHours > $sumaHoras){
                                    $lRows[$j-1]->extraDoubleMins = $lRows[$j-1]->extraDoubleMins + 30;
                                //si la suma de horas es mayor al limite -> media hora triple
                                }else{
                                    $lRows[$j-1]->extraTripleMins = $lRows[$j-1]->extraTripleMins + 30;
                                }
                            }   
                        }
                        if($cambioEmpleado == 1){
                            $auxIni = Carbon::parse($sStartDate);;
                            $auxFin = Carbon::parse($sStartDate);;
                        }
                        $sumaHoras = 0;
                        $HalfPendient = 0;
                        $horasCompletas = 0;
                        $horasMedias = 0;
                }
                break;
            default:
                break;
        }
        SInfoWithPolicy::selectInfo($lRows,$typeInfo);
      }
      //const ALL_DATA = "1";
      //const LIMITED_DATA = "2";
      //const OTHER_DATA = "3";
      public static function selectInfo($lRows, $typeInfo){
        switch($typeInfo){
            case 1:
                for( $j = 0 ; count($lRows) > $j ; $j++ ){
                    $lRows[$j]->extraDoble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    $lRows[$j]->extraTriple = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraTripleMins);
                }
                break;
            case 2:
                for( $j = 0 ; count($lRows) > $j ; $j ){
                    $mediaHora = $lRows[$j]->extraDoubleMins % 60;
                    $horas = intdiv($lRows[$j]->extraDoubleMins,60);
                    if( $mediaHora != 0){
                        $lRows[$j]->extraDoubleMins = $lRows[$j]->extraDoubleMins - 30;
                    }
                    $lRows[$j]->extraDoble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    if($lRows[$j]->extraTripleMins != 0){
                        $salidaMaquillada = Carbon::parse($lRow[$j]->outDateTimeSch);
                        $salidaMaquillada->addHours($horas);
                        $lRow[$j]->outDateTime = $salidaMaquillada;
                    }
                    $lRows[$j]->extraTripleMins = 0;
                }
                break;
            case 3:
                for( $j = 0 ; count($lRows) > $j ; $j ){
                    $mediaHora =  $lRows[$j]->extraDoubleMins % 60;
                    if( $mediaHora != 0){
                        $lRows[$j]->extraDoubleMins = 30;
                        $lRows[$j]->extraDoble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    }
                    $lRows[$j]->extraTriple = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraTripleMins);   
                }
                break;
            default:
                break;
        }
        return $lRows;
      }

    }
?>
