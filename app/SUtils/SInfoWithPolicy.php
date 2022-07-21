<?php namespace App\SUtils;
use DataTime;
use Carbon\Carbon;
use DB;
use App\Http\Controllers\prePayrollController;
use App\SUtils\SDateTimeUtils;
use App\SUtils\SRegistryRow;
use App\SData\SDataProcess;
use App\SUtils\SDateUtils;
use App\Models\processed_data;
use App\Models\period_processed;
use App\Models\prepayrollchange;

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
                        }else{$j++;
                            if($j >= count($lRows)){
                                $auxIni = Carbon::parse($sEndDate);
                                $auxIni->addDay(); 
                            }
                        }
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
                                $horaEntrada = Carbon::parse($lRows[$j]->inDateTime);
                                $horaSalida = Carbon::parse($lRows[$j]->outDateTime);
                                
                                //$diferenciaHoras = $horaEntrada->diffInMinutes($horaSalida);
                                //$diferenciaHoras = $diferenciaHoras - $lRows[$j]->overScheduleMins;
                                //$banderaHoras = 0; 
                                //if($diferenciaHoras < 480 && $lRows[$j]->overScheduleMins > 0){ $banderaHoras = 1;}
                                //$extraProg = $lRows[$j]->overDefaultMins - $lRows[$j]->overScheduleMins;
                                $minutosExtra = $lRows[$j]->overDefaultMins + $lRows[$j]->overWorkedMins;
                                
                                // si tiene más de una hora de tiempo extra
                                if( $minutosExtra >= 60 ){
                                    $horasCompletas = intdiv($minutosExtra,60);
                                    $horasMedias = $minutosExtra % 60;
                                    $auxHorasCompletas = 0;
                                    $auxHorasMedias = 0;
                                    if( $horasMedias >= $initialLimitHour && $finalLimitHour >= $horasMedias ){
                                        $auxHorasCompletas = 1; 
                                    }else if( $horasMedias >= $initialLimitHalf && $initialLimitHour <= $horasMedias ){
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
                                            $sumaHoras = $limitHours;
                                            $lRows[$j]->extraDoubleMins = $horasDentroLimite*60;
                                            $lRows[$j]->extraTripleMins = 0;
                                            
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                            
                                            $lRows[$j]->extraTripleMinsNoficial = $horasFueraLimite*60;
                                        //si no se pasa continua normal
                                        }else{
                                            $sumaHoras = $sumaHorasAuxiliar;
                                            $lRows[$j]->extraDoubleMins = ($horasCompletas + $auxHorasCompletas) * 60;
                                            $lRows[$j]->extraTripleMins = 0;
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                            $lRows[$j]->extraTripleMinsNoficial = 0;
                                        }    
                                    //si la suma de horas paso el limite
                                    }else{
                                        $horasCompletas = ($horasCompletas + $auxHorasCompletas)*60;
                                        $lRows[$j]->extraDoubleMins = 0;
                                        $lRows[$j]->extraTripleMins = 0;
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                        $lRows[$j]->extraTripleMinsNoficial = $horasCompletas;     
                                    }  
                                //si tiene menos de una hora de tiempo extra
                                }else{
                                    // si supera los limites para ser una hora
                                    if( $minutosExtra >= $initialLimitHour && $finalLimitHour >= $minutosExtra){
                                        //si la suma de horas aun no pasa el limite
                                        if($sumaHoras < $limitHours){
                                            $lRows[$j]->extraDoubleMins = 60;
                                            $lRows[$j]->extraTripleMins = 0;
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                            $lRows[$j]->extraTripleMinsNoficial = 0;
                                            $sumaHoras = $sumaHoras + 1;
                                        //si la suma de horas paso el limite
                                        }else{
                                            $lRows[$j]->extraDoubleMins = 0;
                                            $lRows[$j]->extraTripleMins = 0;
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                            $lRows[$j]->extraTripleMinsNoficial = 60; 
                                        }
                                    //si supera los limites para ser media hora
                                    }else if( $minutosExtra >= $initialLimitHalf && $initialLimitHour >= $minutosExtra ){
                                        //se viene arrastrando una media hora
                                        if( $HalfPendient == 1 ){
                                            //si la suma de horas esta por debajo del limite
                                            if( $sumaHoras < $limitHours ){
                                                $lRows[$j]->extraDoubleMins = 60;
                                                $lRows[$j]->extraTripleMins = 0;
                                                $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                                $lRows[$j]->extraTripleMinsNoficial = 0; 
                                                $sumaHoras = $sumaHoras + 1;  
                                                $HalfPendient = 0; 
                                            //si la suma de horas es mayor al limite
                                            }else{
                                                $lRows[$j]->extraDoubleMins = 0;
                                                $lRows[$j]->extraTripleMins = 0;
                                                $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                                $lRows[$j]->extraTripleMinsNoficial = 60; 
                                                $HalfPendient = 0;
                                            }
                                        //Si no se tenia media hora
                                        }else{
                                            $HalfPendient = 1;
                                            $lRows[$j]->extraDoubleMins = 0;
                                            $lRows[$j]->extraTripleMins = 0;
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                            $lRows[$j]->extraTripleMinsNoficial = 0;
                                        }
                                    //no alcanza los limites para ser hora o media hora extra
                                    }else{
                                        $lRows[$j]->extraDoubleMins = 0;
                                        $lRows[$j]->extraTripleMins = 0;
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                        $lRows[$j]->extraTripleMinsNoficial = 0;
                                    }
                                }
                                $j++;
                                if($j >= count($lRows)){
                                    $auxIni = Carbon::parse($sEndDate);
                                    $auxIni->addDays(10); 
                                }
                                $auxIni->addDay();
                            }else if($auxIni > $auxComparacion){
                                if($cambioEmpleado == 1){
                                    $auxIni = Carbon::parse($sEndDate);
                                    $auxIni->addDays(10);
                                    
                                }else{
                                    $j++;
                                    if($j >= count($lRows)){
                                        $auxIni = Carbon::parse($sEndDate);
                                        $auxIni->addDay(10); 
                                    }
                                }    
                            }else{
                                $auxIni->addDay();
                            }
                        
                            //si es el ultimo dia del grupo y se tiene una media hora sobrante 
                            if($auxIni > $auxFin && $HalfPendient == 1){
                                //si la suma de horas es menor al limite -> media hora Double
                                if($limitHours > $sumaHoras){
                                    $lRows[$j-1]->extraDoubleMinsNoficial = $lRows[$j-1]->extraDoubleMinsNoficial + 30;
                                //si la suma de horas es mayor al limite -> media hora triple
                                }else{
                                    $lRows[$j-1]->extraTripleMinsNoficial = $lRows[$j-1]->extraTripleMinsNoficial + 30;
                                }
                            }   
                        }
                        if($cambioEmpleado == 1){
                            $auxIni = Carbon::parse($sStartDate);
                            $auxFin = Carbon::parse($sStartDate);
                            $cambioEmpleado = 0;
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
        return SInfoWithPolicy::selectInfo($lRows,$typeInfo);


      }
      //const ALL_DATA = "1";
      //const LIMITED_DATA = "2";
      //const OTHER_DATA = "3";
      public static function selectInfo($lRows, $typeInfo){
          $sumatoriaD = 0;
          $sumatoriaT = 0;
        switch($typeInfo){
            case 1:
                for( $j = 0 ; count($lRows) > $j ; $j++ ){
                    $sumatoriaD = $lRows[$j]->extraDoubleMins + $lRows[$j]->extraDoubleMinsNoficial;
                    $sumatoriaT = $lRows[$j]->extraTripleMins + $lRows[$j]->extraTripleMinsNoficial;
                    $lRows[$j]->extraDoubleMins = $sumatoriaD;
                    $lRows[$j]->extraTripleMins = $sumatoriaT;
                    $lRows[$j]->extraDouble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    $lRows[$j]->extraTriple = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraTripleMins);
                }
                break;
            case 2:
                for( $j = 0 ; count($lRows) > $j ; $j++ ){
                    $mediaHora = $lRows[$j]->extraDoubleMinsNoficial % 60;
                    $horasD = intdiv($lRows[$j]->extraDoubleMinsNoficial,60);
                    $horasT = intdiv($lRows[$j]->extraTripleMinsNoficial,60);
                    if($lRows[$j]->extraDoubleMinsNoficial > 30){
                        if($lRows[$j]->cutId == 2){
                            $Maquillada = Carbon::parse($lRows[$j]->inDateTime);
                            $minutos = $Maquillada->format('i');
                            $minutos = trim($minutos, "0");
                            $horas = $Maquillada->format('h');
                            $horas = trim($horas, "0");
                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $Maquillada->addMinutes($minutosA);
                            }else{
                                $Maquillada->subMinutes($minutosA);   
                            }
                            $Maquillada->addHours($horasD);
                            $lRows[$j]->inDateTime = $Maquillada->toDateTimeString();
                            if($lRows[$j]->extraTripleMinsNoficial != 0){
                                $salidaMaquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                                $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                                $minutos = $salidaOriginal->format('i');
                                $minutos = trim($minutos, "0");
                                $horas = $salidaOriginal->format('h');
                                $horas = trim($horas, "0");
                                $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                if($minutosA >= 0){
                                    $salidaMaquillada->addMinutes($minutosA);
                                }else{
                                    $salidaMaquillada->subMinutes($minutosA);   
                                }
                                
                                
                                $lRows[$j]->outDateTime = $salidaMaquillada->toDateTimeString();
                            }   

                        }else{
                            $Maquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                            $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                            $minutos = $salidaOriginal->format('i');
                            $minutos = trim($minutos, "0");
                            $horas = $salidaOriginal->format('h');
                            $horas = trim($horas, "0");
                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $Maquillada->addMinutes($minutosA);
                            }else{
                                $Maquillada->subMinutes($minutosA);   
                            }
                            $Maquillada->subHours($horasD);

                            $lRows[$j]->outDateTime = $Maquillada->toDateTimeString();
                            
                        }

                    }else{
                        if($lRows[$j]->extraTripleMinsNoficial != 0){
                            $salidaMaquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                            $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                            $minutos = $salidaOriginal->format('i');
                            $minutos = trim($minutos, "0");
                            $horas = $salidaOriginal->format('h');
                            $horas = trim($horas, "0");

                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $salidaMaquillada->addMinutes($minutosA);
                            }else{
                                $salidaMaquillada->subMinutes($minutosA);   
                            }
                            
                            
                            $lRows[$j]->outDateTime = $salidaMaquillada->toDateTimeString();
                        }   
                    }
                    
                    $lRows[$j]->extraDouble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    $lRows[$j]->extraTripleMins = 0;
                }
                break;
            case 3:
                for( $j = 0 ; count($lRows) > $j ; $j++ ){ 
                    $lRows[$j]->extraDoubleMins = $lRows[$j]->extraDoubleMinsNoficial;
                    $lRows[$j]->extraTripleMins = $lRows[$j]->extraTripleMinsNoficial;
                    $lRows[$j]->extraDouble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    $lRows[$j]->extraTriple = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraTripleMins);
                }
                break;
            default:
                break;
        }
        return $lRows;
      }

      public static function cutMinutes($hour,$minutes){
        $arr = SInfoWithPolicy::initArr();
        $minutos = $arr[$hour][$minutes];

        return $minutos;
      }

      public static function normalizacion($inilihalf,$inilihour,$finlihour,$minutes){
        if($minutes > 60){  
            $completas = intdiv($minutes,60);
            $minutos =  $minutes % 60;
        }else{
            $completas = 0;
            $minutos = $minutes;
        }

        if($minutos > $inilihalf && $minutos < $inilihour){
            $minutes = ($completas*60) + 30;
        }elseif($minutos > $inilihour && $minutos < $finlihour){
            $minutes = ($completas*60) + 60;
        }else{
            $minutes = $completas*60;
        }
            
        return $minutes;
      }
      
      /**
      * Estandarización de los datos 
      *
      * @param string $sStartDate
      * @param string $sEndDate
      * @param int $payWay [ 1: QUINCENA, 2: SEMANA, 0: TODOS]
      * @param int $type
      * @param int $key
      * @param int $employees

      * @return [SRegistryRow] (array)
      */
      public static function standardization($sStartDate,$sEndDate,$payWay,$employees){
        $lEmployees = SGenUtils::toEmployeeIds(0, 0, null, $employees);
        $lRows = SDataProcess::process($sStartDate, $sEndDate, $payWay, $lEmployees);

        $dateS = Carbon::parse($sStartDate);
        $dateE = Carbon::parse($sEndDate);
        $auxIni = Carbon::parse($sStartDate);
        $auxFin = Carbon::parse($sStartDate);

        $diferencia = ($dateE->diffInDays($dateS))+1;

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
                $contadorDias = 0;
                if($payWay == 1 ){
                    $limitHours = $hrsBiweekly;
                }else if($payWay == 2){
                    $limitHours = $hrsWeekly;
                }
                $extraHoliday = 0;

                for( $j = 0 ; count($lRows) > $j ; $j++ ){
                        //if($lRows[$j]->hasSchedule == true){
                            $minutosExtra = $lRows[$j]->overDefaultMins + $lRows[$j]->overWorkedMins + $lRows[$j]->overMinsByAdjs + $lRows[$j]->overScheduleMins;
                        //}else{
                         //   $minutosExtra = $lRows[$j]->overWorkedMins;
                        //}        
                        // si tiene más de una hora de tiempo extra
                        if( $minutosExtra >= 60 ){
                            $horasCompletas = intdiv($minutosExtra,60);
                            $horasMedias = $minutosExtra % 60;
                            $auxHorasCompletas = 0;
                            $auxHorasMedias = 0;
                            $sumaHorasAuxiliar = 0;
                            if( $horasMedias >= $initialLimitHour && $finalLimitHour >= $horasMedias ){
                                $auxHorasCompletas = 1; 
                            }else if( $horasMedias >= $initialLimitHalf && $initialLimitHour >= $horasMedias ){
                                $auxHorasMedias = 1;
                            }

                            // se quita para no sumar horas medias
                            /*if( $HalfPendient == 1 && $auxHorasMedias == 1){
                                 $auxHorasCompletas = $auxHorasCompletas + 1 ;
                            }else if( $HalfPendient == 0 && $auxHorasMedias == 1){
                                $HalfPendient = 1;
                            }*/
                            // fin 

                            //si en la última pasada la suma de horas no paso el limite
                            if($sumaHoras < $limitHours){
                                if($auxHorasMedias == 1){
                                    $sumaHorasAuxiliar = $sumaHoras + $horasCompletas + $auxHorasCompletas + 0.5;
                                }else{
                                    $sumaHorasAuxiliar = $sumaHoras + $horasCompletas + $auxHorasCompletas;
                                }
                                
                                //checar si se pasa al sumar las horas del dia
                                if($sumaHorasAuxiliar > $limitHours){

                                    $horasFueraLimite = $sumaHorasAuxiliar - $limitHours;
                                    if($auxHorasMedias == 1){
                                        $horasDentroLimite = ($horasCompletas + $auxHorasCompletas + 0.5) - $horasFueraLimite;
                                    }else{
                                        $horasDentroLimite = ($horasCompletas + $auxHorasCompletas) - $horasFueraLimite;
                                    }
                                    
                                    $sumaHoras = $limitHours;
                                    $lRows[$j]->extraDoubleMins = $horasDentroLimite*60;
                                    $lRows[$j]->extraTripleMins = 0;
                                    //se retira porque esos minutos tambien se tomaran en cuenta como los anteriores
                                    /*
                                    if($lRows[$j]->hasSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }elseif($lRows[$j]->isOnSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }else{
                                        $lRows[$j]->extraDoubleMinsNoficial = 0;
                                    }          
                                    */
                                            
                                    $lRows[$j]->extraDoubleMinsNoficial = $horasFueraLimite*60;
                                //si no se pasa continua normal
                                }else{
                                    $sumaHoras = $sumaHorasAuxiliar;
                                    $lRows[$j]->extraDoubleMins = ($horasCompletas + $auxHorasCompletas) * 60;
                                    if($auxHorasMedias == 1){
                                        $lRows[$j]->extraDoubleMins = $lRows[$j]->extraDoubleMins + 30;
                                    }else{
                                        $lRows[$j]->extraDoubleMins = $lRows[$j]->extraDoubleMins;
                                    }
                                    $lRows[$j]->extraTripleMins = 0;
                                    /*
                                    if($lRows[$j]->hasSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }elseif($lRows[$j]->isOnSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }else{
                                        $lRows[$j]->extraDoubleMinsNoficial = 0;
                                    } 
                                    $lRows[$j]->extraTripleMinsNoficial = 0;
                                    */
                                    if((($sumaHoras*60) % 60) != 0 && $auxHorasMedias == 1){
                                        $diasConMediaHora[$contadorDias] = $j; 
                                        $contadorDias++;
                                    }
                                }    
                                //si en la última pasada la suma de horas paso el limite
                            }else{
                                if($auxHorasMedias == 1){
                                    $horasCompletas = $horasCompletas + $auxHorasCompletas + 0.5;
                                }else{
                                    $horasCompletas = $horasCompletas + $auxHorasCompletas;
                                }
                                $lRows[$j]->extraDoubleMins = 0;
                                $lRows[$j]->extraTripleMins = 0;
                                /*
                                if($lRows[$j]->hasSchedule == true){
                                    $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                }elseif($lRows[$j]->isOnSchedule == true){
                                    $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                }else{
                                    $lRows[$j]->extraDoubleMinsNoficial = 0;
                                } 
                                */
                                $lRows[$j]->extraDoubleMinsNoficial = $horasCompletas * 60;     
                            }  
                            //si tiene menos de una hora de tiempo extra
                        }else{
                            // si supera los limites para ser una hora
                            if( $minutosExtra >= $initialLimitHour && $finalLimitHour >= $minutosExtra){
                                //si la suma de horas aun no pasa el limite
                                if($sumaHoras < $limitHours){
                                    $sumaHorasAuxiliar = $sumaHoras + 1;
                                    if($sumaHorasAuxiliar > $limitHours){
                                        $horasFueraLimite = $sumaHorasAuxiliar - $limitHours;
                                        $horasDentroLimite = 0.5;
                                        $sumaHoras = $limitHours;
                                        $lRows[$j]->extraDoubleMins = 30;
                                        $lRows[$j]->extraTripleMins = 0;
                                        $lRows[$j]->extraDoubleMinsNoficial = 30;
                                    }else{
                                        $lRows[$j]->extraDoubleMins = 60;
                                        $lRows[$j]->extraTripleMins = 0;
                                        $lRows[$j]->extraDoubleMinsNoficial = 0; 
                                        $sumaHoras = $sumaHoras + 1;   
                                    }
                                    /*
                                    if($lRows[$j]->hasSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }elseif($lRows[$j]->isOnSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }else{
                                        $lRows[$j]->extraDoubleMinsNoficial = 0;
                                    }
                                    */
                                    
                                //si la suma de horas paso el limite
                                }else{
                                    $lRows[$j]->extraDoubleMins = 0;
                                    $lRows[$j]->extraTripleMins = 0;
                                    /*
                                    if($lRows[$j]->hasSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }elseif($lRows[$j]->isOnSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }else{
                                        $lRows[$j]->extraDoubleMinsNoficial = 0;
                                    }
                                    */ 
                                    $lRows[$j]->extraDoubleMinsNoficial = 60; 
                                }
                                //si supera los limites para ser media hora
                            }else if( $minutosExtra >= $initialLimitHalf && $initialLimitHour >= $minutosExtra ){
                                /*
                                //se viene arrastrando una media hora
                                
                                if( $HalfPendient == 1 ){
                                    //si la suma de horas esta por debajo del limite
                                    if( $sumaHoras < $limitHours ){
                                        $lRows[$j]->extraDoubleMins = 60;
                                        $lRows[$j]->extraTripleMins = 0;
                                        if($lRows[$j]->hasSchedule == true){
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                        }elseif($lRows[$j]->isOnSchedule == true){
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                        }else{
                                            $lRows[$j]->extraDoubleMinsNoficial = 0;
                                        } 
                                        $lRows[$j]->extraTripleMinsNoficial = 0; 
                                        $sumaHoras = $sumaHoras + 1;  
                                        $HalfPendient = 0; 
                                    //si la suma de horas es mayor al limite
                                    }else{
                                        $lRows[$j]->extraDoubleMins = 0;
                                        $lRows[$j]->extraTripleMins = 0;
                                        if($lRows[$j]->hasSchedule == true){
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                        }elseif($lRows[$j]->isOnSchedule == true){
                                            $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                        }else{
                                            $lRows[$j]->extraDoubleMinsNoficial = 0;
                                        } 
                                        $lRows[$j]->extraTripleMinsNoficial = 60; 
                                        $HalfPendient = 0;
                                    }
                                    //Si no se tenia media hora
                                }else{
                                    $HalfPendient = 1;
                                    $lRows[$j]->extraDoubleMins = 0;
                                    $lRows[$j]->extraTripleMins = 0;
                                    if($lRows[$j]->hasSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }elseif($lRows[$j]->isOnSchedule == true){
                                        $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                    }else{
                                        $lRows[$j]->extraDoubleMinsNoficial = 0;
                                    } 
                                    $lRows[$j]->extraTripleMinsNoficial = 0;
                                }
                                //no alcanza los limites para ser hora o media hora extra
                                */
                                if( $sumaHoras < $limitHours ){
                                    $lRows[$j]->extraDoubleMins = 30;
                                    $lRows[$j]->extraTripleMins = 0;
                                    $lRows[$j]->extraDoubleMinsNoficial = 0; 
                                    $sumaHoras = $sumaHoras + 0.5;
                                    if((($sumaHoras*60) % 60) != 0){
                                        $diasConMediaHora[$contadorDias] = $j;
                                        $contadorDias++; 
                                    }
                                }else{
                                    $lRows[$j]->extraDoubleMins = 0;
                                    $lRows[$j]->extraTripleMins = 0;
                                    $lRows[$j]->extraDoubleMinsNoficial = 30;
                                }
                            }else{
                                $lRows[$j]->extraDoubleMins = 0;
                                $lRows[$j]->extraTripleMins = 0;
                                 /*
                                if($lRows[$j]->hasSchedule == true){
                                    $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                }elseif($lRows[$j]->isOnSchedule == true){
                                    $lRows[$j]->extraDoubleMinsNoficial = SInfoWithPolicy::normalizacion($initialLimitHalf,$initialLimitHour,$finalLimitHour,$lRows[$j]->overScheduleMins);
                                }else{
                                    $lRows[$j]->extraDoubleMinsNoficial = 0;
                                }
                                */ 
                                $lRows[$j]->extraDoubleMinsNoficial = 0;
                            }
                        }        
                    /*
                    //si es el ultimo dia del grupo y se tiene una media hora sobrante 
                    if($auxIni > $auxFin && $HalfPendient == 1){
                    //si la suma de horas es menor al limite -> media hora Double
                        if($limitHours > $sumaHoras){
                            $lRows[$j-1]->extraDoubleMinsNoficial = $lRows[$j-1]->extraDoubleMinsNoficial + 30;
                        //si la suma de horas es mayor al limite -> media hora triple
                        }else{
                            $lRows[$j-1]->extraTripleMinsNoficial = $lRows[$j-1]->extraTripleMinsNoficial + 30;
                        }
                    }
                    */
                      
                }
                if((($sumaHoras*60) % 60) != 0 ){
                    $lRows[$diasConMediaHora[0]]->extraDoubleMins = $lRows[$diasConMediaHora[0]]->extraDoubleMins - 30;
                    $lRows[$diasConMediaHora[0]]->extraDoubleMinsNoficial = 30;
                }
            break;
            default:
            break;
        }
        
        return $lRows;
      }

      public static function handlingHours($lRows,$diferencia,$extratime){
        $config = \App\SUtils\SConfiguration::getConfigurations();
        switch($config->policy_cut){
            case 1:
                if($extratime != 1){

                    for( $j = 0 ; count($lRows) > $j ; $j++ ){
                        if($lRows[$j]->workable == 0){
                            $lRows[$j]->isDayOff = 1;
                            if($lRows[$j]->hasChecks == 0){
                                $lRows[$j]->work_dayoff = 0;
                            }else{
                                $lRows[$j]->work_dayoff = 1;
                            }
                        }
                        if($lRows[$j]->extraDoubleMinsNoficial != 0 || $lRows[$j]->extraTripleMinsNoficial != 0 || $lRows[$j]->extraDoubleMinsNoficial != 0 || $lRows[$j]->extraTripleMinsNoficial != 0){
                            $mediaHoraD = $lRows[$j]->extraDoubleMinsNoficial % 60;
                            $mediaHoraT = $lRows[$j]->extraTripleMinsNoficial % 60;
                            $horasD = intdiv($lRows[$j]->extraDoubleMinsNoficial,60);
                            $horasT = intdiv($lRows[$j]->extraTripleMinsNoficial,60);
                            if($lRows[$j]->cutId == 2){
                                $Maquillada = Carbon::parse($lRows[$j]->inDateTime);
                                $minutos = $Maquillada->format('i');
                                if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                                $horas = $Maquillada->format('h');
                                $horas = trim($horas, "0");
                                $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                if($minutosA >= 0){
                                    $Maquillada->addMinutes($minutosA);
                                }else{
                                    $Maquillada->subMinutes($minutosA);   
                                }
                                $Maquillada->addHours($horasD);
                                $Maquillada->addHours($horasT);
                                $Maquillada->addMinutes($mediaHoraD);
                                $Maquillada->addMinutes($mediaHoraT);
                                $lRows[$j]->inDateTimeNoficial = $Maquillada->toDateTimeString();
                            }else{
                                //$Maquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                                $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                                $minutos = $salidaOriginal->format('i');
                                if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                                
                                $horas = $salidaOriginal->format('h');
                                $horas = trim($horas, "0");
                                $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                if($minutosA >= 0){
                                    $salidaOriginal->addMinutes($minutosA);
                                }else{
                                    $salidaOriginal->subMinutes($minutosA);   
                                }
                                $salidaOriginal->subHours($horasD);
                                $salidaOriginal->subHours($horasT);
                                $salidaOriginal->subMinutes($mediaHoraD);
                                $salidaOriginal->subMinutes($mediaHoraT);

                                $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                            
                            }
                        }    
                        $lRows[$j]->extraDouble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                        $lRows[$j]->extraTripleMins = 0;
                            
                        /*
                        $mediaHora = $lRows[$j]->extraDoubleMinsNoficial % 60;
                        $horasD = intdiv($lRows[$j]->extraDoubleMinsNoficial,60);
                        $horasT = intdiv($lRows[$j]->extraTripleMinsNoficial,60);
                        if($lRows[$j]->extraDoubleMinsNoficial > 30){
                            if($lRows[$j]->cutId == 2){
                                $Maquillada = Carbon::parse($lRows[$j]->inDateTime);
                                $minutos = $Maquillada->format('i');
                                if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                                $horas = $Maquillada->format('h');
                                $horas = trim($horas, "0");
                                $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                if($minutosA >= 0){
                                    $Maquillada->addMinutes($minutosA);
                                }else{
                                    $Maquillada->subMinutes($minutosA);   
                                }
                                $Maquillada->addHours($horasD);
                                $lRows[$j]->inDateTimeNoficial = $Maquillada->toDateTimeString();
                                if($lRows[$j]->extraTripleMinsNoficial != 0){
                                    //$salidaMaquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                                    $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                                    $minutos = $salidaOriginal->format('i');
                                    if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                                    $horas = $salidaOriginal->format('h');
                                    $horas = trim($horas, "0");
                                    $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                    if($minutosA >= 0){
                                        $salidaOriginal->addMinutes($minutosA);
                                    }else{
                                        $salidaOriginal->subMinutes($minutosA);   
                                    }
                                    
                                    $salidaOrignial->subHours($horasT);
                                    $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                                }   

                            }else{
                                //$Maquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                                $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                                $minutos = $salidaOriginal->format('i');
                                if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                                
                                $horas = $salidaOriginal->format('h');
                                $horas = trim($horas, "0");
                                $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                if($minutosA >= 0){
                                    $salidaOriginal->addMinutes($minutosA);
                                }else{
                                    $salidaOriginal->subMinutes($minutosA);   
                                }
                                $salidaOriginal->subHours($horasD);

                                $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                                
                            }

                        }else{
                            if($lRows[$j]->extraTripleMinsNoficial != 0){
                                //$salidaMaquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                                $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                                $minutos = $salidaOriginal->format('i');
                                if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                                $horas = $salidaOriginal->format('h');
                                $horas = trim($horas, "0");

                                $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                if($minutosA >= 0){
                                    $salidaOriginal->addMinutes($minutosA);
                                }else{
                                    $salidaOriginal->subMinutes($minutosA);   
                                }
                                
                                $salidaOriginal->subHours($horasT);
                                $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                            }   
                        }
                        
                        $lRows[$j]->extraDouble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                        $lRows[$j]->extraTripleMins = 0; */
                    }
                }else{
                    for( $j = 0 ; count($lRows) > $j ; $j++ ){
                        if($lRows[$j]->workable == 0){
                            $lRows[$j]->isDayOff = 1;
                            if($lRows[$j]->hasChecks == 0){
                                $lRows[$j]->work_dayoff = 0;
                            }else{
                                $lRows[$j]->work_dayoff = 1;
                            }
                        }
                        if($lRows[$j]->outDateTimeSch != null){
                            $salidaO = Carbon::parse($lRows[$j]->outDateTime);
                            $salidaP = Carbon::parse($lRows[$j]->outDateTimeSch);

                            $horaO = $salidaO->format('h');
                            $horaP = $salidaP->format('h');
                            $minutoO = $salidaO->format('i');
                            $minutoP = $salidaP->format('i');

                            if($minutoO == "00"){$minutoO = "0";}else{$minutoO = trim($minutoO, "0");}
                            if($minutoP == "00"){$minutoP = "0";}else{$minutoP = trim($minutoP, "0");}
                            
                            $horaO = trim($horaO, "0");
                            $horaP = trim($horaP, "0");

                            $diferenciaH = $horaO - $horaP;
                            $diferenciaM = $minutoO - $minutoP;

                            

                            if( $diferenciaH > 1 || $diferenciaM >20){
                                $minutosA = SInfoWithPolicy::cutMinutes($horaO,$minutoO);
                                if($minutosA >= 0){
                                    $salidaP->addMinutes($minutosA);
                                }else{
                                    $salidaP->subMinutes($minutosA);   
                                }
                                $lRows[$j]->outDateTimeNoficial = $salidaP->toDateTimeString();
                            }

                            
                        }
                    }    
                }
            break;
            case 2:
                for( $j = 0 ; count($lRows) > $j ; $j++ ){
                    if($lRows[$j]->workable == 0){
                        $lRows[$j]->isDayOff = 1;
                        if($lRows[$j]->hasChecks == 0){
                            $lRows[$j]->work_dayoff = 0;
                        }else{
                            $lRows[$j]->work_dayoff = 1;
                        }
                    }
                    if($lRows[$j]->extraDoubleMinsNoficial != 0 || $lRows[$j]->extraTripleMinsNoficial != 0 || $lRows[$j]->extraDoubleMinsNoficial != 0 || $lRows[$j]->extraTripleMinsNoficial != 0){
                        $mediaHoraD = $lRows[$j]->extraDoubleMinsNoficial % 60;
                        $mediaHoraT = $lRows[$j]->extraTripleMinsNoficial % 60;
                        $horasD = intdiv($lRows[$j]->extraDoubleMinsNoficial,60);
                        $horasT = intdiv($lRows[$j]->extraTripleMinsNoficial,60);
                        if($lRows[$j]->cutId == 2){
                            $Maquillada = Carbon::parse($lRows[$j]->inDateTime);
                            $minutos = $Maquillada->format('i');
                            if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                            $horas = $Maquillada->format('h');
                            $horas = trim($horas, "0");
                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $Maquillada->addMinutes($minutosA);
                            }else{
                                $Maquillada->subMinutes($minutosA);   
                            }
                            $Maquillada->addHours($horasD);
                            $Maquillada->addHours($horasT);
                            $Maquillada->addMinutes($mediaHoraD);
                            $Maquillada->addMinutes($mediaHoraT);
                            $lRows[$j]->inDateTimeNoficial = $Maquillada->toDateTimeString();
                        }else{
                            //$Maquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                            $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                            $minutos = $salidaOriginal->format('i');
                            if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                        
                            $horas = $salidaOriginal->format('h');
                            $horas = trim($horas, "0");
                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $salidaOriginal->addMinutes($minutosA);
                            }else{
                                $salidaOriginal->subMinutes($minutosA);   
                            }
                            $salidaOriginal->subHours($horasD);
                            $salidaOriginal->subHours($horasT);
                            $salidaOriginal->subMinutes($mediaHoraD);
                            $salidaOriginal->subMinutes($mediaHoraT);

                            $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                        
                        }
                    }    
                    $lRows[$j]->extraDouble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    $lRows[$j]->extraTripleMins = 0;
                        
                    /*
                    $mediaHora = $lRows[$j]->extraDoubleMinsNoficial % 60;
                    $horasD = intdiv($lRows[$j]->extraDoubleMinsNoficial,60);
                    $horasT = intdiv($lRows[$j]->extraTripleMinsNoficial,60);
                    if($lRows[$j]->extraDoubleMinsNoficial > 30){
                        if($lRows[$j]->cutId == 2){
                            $Maquillada = Carbon::parse($lRows[$j]->inDateTime);
                            $minutos = $Maquillada->format('i');
                            if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                            $horas = $Maquillada->format('h');
                            $horas = trim($horas, "0");
                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $Maquillada->addMinutes($minutosA);
                            }else{
                                $Maquillada->subMinutes($minutosA);   
                            }
                            $Maquillada->addHours($horasD);
                            $lRows[$j]->inDateTimeNoficial = $Maquillada->toDateTimeString();
                            if($lRows[$j]->extraTripleMinsNoficial != 0){
                                //$salidaMaquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                                $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                                $minutos = $salidaOriginal->format('i');
                                if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                                $horas = $salidaOriginal->format('h');
                                $horas = trim($horas, "0");
                                $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                                if($minutosA >= 0){
                                    $salidaOriginal->addMinutes($minutosA);
                                }else{
                                    $salidaOriginal->subMinutes($minutosA);   
                                }
                                
                                $salidaOrignial->subHours($horasT);
                                $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                            }   

                        }else{
                            //$Maquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                            $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                            $minutos = $salidaOriginal->format('i');
                            if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                            
                            $horas = $salidaOriginal->format('h');
                            $horas = trim($horas, "0");
                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $salidaOriginal->addMinutes($minutosA);
                            }else{
                                $salidaOriginal->subMinutes($minutosA);   
                            }
                            $salidaOriginal->subHours($horasD);

                            $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                            
                        }

                    }else{
                        if($lRows[$j]->extraTripleMinsNoficial != 0){
                            //$salidaMaquillada = Carbon::parse($lRows[$j]->outDateTimeSch);
                            $salidaOriginal = Carbon::parse($lRows[$j]->outDateTime);
                            $minutos = $salidaOriginal->format('i');
                            if($minutos == "00"){$minutos = "0";}else{$minutos = trim($minutos, "0");}
                            $horas = $salidaOriginal->format('h');
                            $horas = trim($horas, "0");

                            $minutosA = SInfoWithPolicy::cutMinutes($horas,$minutos);
                            if($minutosA >= 0){
                                $salidaOriginal->addMinutes($minutosA);
                            }else{
                                $salidaOriginal->subMinutes($minutosA);   
                            }
                            
                            $salidaOriginal->subHours($horasT);
                            $lRows[$j]->outDateTimeNoficial = $salidaOriginal->toDateTimeString();
                        }   
                    }
                    
                    $lRows[$j]->extraDouble = SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDoubleMins);
                    $lRows[$j]->extraTripleMins = 0; */
                }
            break;
        }
        return $lRows;  
      }

      public static function restDay($lRows,$diferencia){
        $regla = 2;
        switch($regla){
            case 1:
                $semanaNoCompleta = false;
                $horaExtraMenor = 0;
                $extraMenorPosicion = 0;
                $posicionRango = 0;
                $auxCorrectos = 0;
                $contadorAusencia = 0;
                $contadorHoliday = 0;
                $contadorSinextra = 0;
                $banderaAjuste = 0;
                // si son 7 días se procesa para sacar día de descanso.
                if($diferencia != 7){ return $lRows;}
                $days = 0;
                for($i= 0 ; count($lRows) > $i ; $i++){
                    if($lRows[$i]->isDayRepeated == false){
                        $days++; 
                    }
                }
                if($days < 7){ return $lRows;};
                for($i = 0 ; count($lRows) > $i ; $i++){
                    $banderaAjuste = 0;
                    if($lRows[$i]->workable == 1){
                        if($lRows[$i]->isDayRepeated == false){
                            //checar si tiene ajuste
                            if(count($lRows[$i]->adjusts) != 0){
                                for($x = 0 ; count($lRows[$i]->adjusts) > $x ; $x++){
                                    if($lRows[$i]->adjusts[$x]->adjust_type_id == 4){
                                        $banderaAjuste = 1;
                                        $lRows[$i]->hasAdjust = true;
                                    }    
                                }
                            }
                            if( $lRows[$i]->hasChecks == false && $banderaAjuste == 0){
                                if( $lRows[$i]->hasAbsence == true ){
                                    $aAbsence[$contadorAusencia] = $i;
                                    $contadorAusencia++;
                                }elseif( $lRows[$i]->isHoliday == true ){
                                    if($lRows[$i]->extraDoubleMins > 0){
                                        $aHoliday[$contadorHoliday] = $i;
                                        $contadorHoliday++;
                                    }
                                }
                                else{
                                    if(sizeof($lRows[$i]->events)<1){
                                        //$lRows[$i]->hasAbsence = true;
                                    }
                                    $semanaNoCompleta = true;
                                }
                            
                            }elseif( $lRows[$i]->hasChecks == true || $banderaAjuste == 1){
                                if( $lRows[$i]->extraDoubleMins == 0 && $lRows[$i]->extraTripleMins == 0 && $lRows[$i]->isSunday == 0){
                                    $aWithoutExtra[$contadorSinextra] = $i;
                                    $contadorSinextra++;
                                }else{
                                    $extraTotales = $lRows[$i]->extraDoubleMins +  $lRows[$i]->extraTripleMins;
                                    if( ( $horaExtraMenor > $extraTotales || $horaExtraMenor == 0 ) && $lRows[$i]->isSunday == 0 ){
                                        $horaExtraMenor = $extraTotales;
                                        $extraMenorPosicion = $i;
                                    }
                                }
                                if( $lRows[$i]->isHoliday == true ){
                                    if($lRows[$i]->extraDoubleMins > 0){
                                        $aHoliday[$contadorHoliday] = $i;
                                        $contadorHoliday++;
                                    }
                                }else{
                                    $diasCorrectos[$auxCorrectos] = $i;
                                    $auxCorrectos++;
                                }
                                
                            }
                        }
                    }else{
                        $lRows[$i]->isDayOff = 1;
                        if($lRows[$i]->hasChecks == 0){
                            $lRows[$i]->work_dayoff = 0;
                        }else{
                            $lRows[$i]->work_dayoff = 1;
                        }

                        $semanaNoCompleta = true;
                    }
                }
                $diaSumar = 0;
                if($semanaNoCompleta != true){
                    if(isset($aAbsence)){
                        $lRows[$aAbsence[0]]->isDayOff = 1;
                        if($lRows[$aAbsence[0]]->hasChecks == 0){
                            $lRows[$aAbsence[0]]->work_dayoff = 0;
                        }else{
                            $lRows[$aAbsence[0]]->work_dayoff = 1;
                        }
                        $lRows[$aAbsence[0]]->hasAbsence = false;
                    }else{
                            if(isset($aWithoutExtra)){
                                $lRows[$aWithoutExtra[0]]->isDayOff = 1;
                                if($lRows[$aWithoutExtra[0]]->hasChecks == 0){
                                    $lRows[$aWithoutExtra[0]]->work_dayoff = 0;
                                }else{
                                    $lRows[$aWithoutExtra[0]]->work_dayoff = 1;
                                }
                                if( $aWithoutExtra[0] == $diasCorrectos[0] ){ $diaSumar = 1;}
                            }else{
                                $lRows[$extraMenorPosicion]->isDayOff = 1;
                                if($lRows[$extraMenorPosicion]->hasChecks == 0){
                                    $lRows[$extraMenorPosicion]->work_dayoff = 0;
                                }else{
                                    $lRows[$extraMenorPosicion]->work_dayoff = 1;
                                } 
                                if( $diasCorrectos[0] == $extraMenorPosicion ){
                                    $diaSumar = 1;
                                }else{
                                    $diaSumar = 0;
                                }
                                
                                $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$extraMenorPosicion ]->extraDoubleMins;
                                $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$extraMenorPosicion ]->extraTripleMins;
                                
                                if($lRows[$extraMenorPosicion ]->extraDoubleMins > 0){
                                    if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                        $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                        $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                        $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;

                                        $salidaMaquillada->addMinutes($minutos);
                                        $salidaMaquillada->addHours($horas);

                                        $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                        
                                    }else{
                                        $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                        $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                        $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;

                                        $salidaMaquillada->addMinutes($minutos);
                                        $salidaMaquillada->addHours($horas);

                                        $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                    }
                                    
                                }
                                $lRows[$extraMenorPosicion ]->extraDoubleMins = 0;
                                $lRows[$extraMenorPosicion ]->extraTripleMins = 0;
                            }
                    }
                    if($contadorHoliday > 0){
                        for($i = 0 ; count($aHoliday) > $i ; $i++){
                            $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[ $aHoliday[$i] ]->extraDoubleMins;
                            $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[ $aHoliday[$i] ]->extraTripleMins;
                            
                            if($lRows[ $aHoliday[$i] ]->extraDoubleMins > 0){
                                if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                    $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                    $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;

                                    $salidaMaquillada->addMinutes($minutos);
                                    $salidaMaquillada->addHours($horas);

                                    $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                    
                                }else{
                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                    $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                    $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;

                                    $salidaMaquillada->addMinutes($minutos);
                                    $salidaMaquillada->addHours($horas);

                                    $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                }
                                
                            }
                            $lRows[ $aHoliday[$i] ]->extraDoubleMins = 0;
                            $lRows[ $aHoliday[$i] ]->extraTripleMins = 0;    
                        }
                    }   
                }
                return $lRows;
                break;
            case 2:
                $semanaNoCompleta = false;
                $horaExtraMenor = 0;
                $extraMenorPosicion = 0;
                $posicionRango = 0;
                $auxCorrectos = 0;
                $contadorAusencia = 0;
                $contadorHoliday = 0;
                $contadorSinextra = 0;
                $contadorMenor = 0;
                $banderaAjuste = 0;
                $haveDayoff = 0;
                $missingAbsence = 0;
                $diaAnteriorDescanso = 0;
                $semanaBase = Carbon::parse($lRows[0]->inDate);
                $primerAntes = Carbon::parse($lRows[0]->inDate);
                $ultimoAntes = Carbon::parse($lRows[0]->inDate);
                $primerDespues = Carbon::parse($lRows[0]->inDate);
                $ultimoDespues = Carbon::parse($lRows[0]->inDate);
                //Arreglo con horas extra menores y con orden
                $horasExtraMenor = [];
                $posicionMenor = [];
                // Fechas de semana anterior
                $primerAntes->subDays(7);
                $ultimoAntes->subDays(1);

                $cadenaAP = $primerAntes->toDateString();
                $cadenaAU = $ultimoAntes->toDateString();
                //Descanso en semana anterior
                $diasDescanso = DB::table('processed_data')
                                    ->whereIn('inDate',[$cadenaAP,$cadenaAU])
                                    ->where('employee_id',$lRows[0]->idEmployee)
                                    ->select('outDate AS fecha','is_dayoff')
                                    ->get();
                //sacar que dia tuvo descanso
                if(count($diasDescanso) != 0){
                    for( $i = 0 ; count($diasDescanso) > $i ; $i++ ){
                        if($diasDescanso[$i]->is_dayoff == 1){
                            $diaAnteriorDescanso = $i;
                        }     
                    }
                }else{
                    $diaAnteriorDescanso = 0;
                }

                $config = \App\SUtils\SConfiguration::getConfigurations();
                //dia optimo para cumplir con descanso cada 7 dias
                $diasOptimo = $diaAnteriorDescanso;
                $contador = $diasOptimo;
                $diasAntes = [];
                $diasDespues = [];
                // conjunto de días que cumplirian con la regla antes del optimo
                for( $i = 0 ; $config->stps_days > $i ; $i++ ){
                    $contador = $contador - 1;
                    if($contador < 0){
                        $i = 7;
                    }else{
                        $diasAntes [$i] = $contador;
                    }
                }
                $contador = $diasOptimo;
                
                //conjunto de días que cumplirian con la regla despues del optimo
                for( $i = 0 ; $config->stps_days > $i ; $i++ ){
                    $contador = $contador + 1;
                    if($contador < 7){
                        $diasDespues [$i] = $contador; 
                    }else{
                        $i = 7;
                    }
                }
                //Fecha de semana posterior
                $primerDespues->addDays(7);
                $ultimoDespues->addDays(13);
                //Descanso en semana posterior
                
                                                

                // si son 7 días se procesa para sacar día de descanso.
                if($diferencia != 7){ return $lRows;}
                $days = 0;
                for($i= 0 ; count($lRows) > $i ; $i++){
                    if($lRows[$i]->isDayRepeated == false){
                        $days++; 
                    }
                }
                
                // se quita porque no es necesario que sean 7 días.
                //if($days < 7){ return $lRows;};
                
                $descansoImpl = 0;


                for($i = 0 ; count($lRows) > $i ; $i++){
                    $banderaAjuste = 0;
                    $dayA = [];
                    $dayA = days_works_array(1);
                    if($lRow[$i]->hasChecks == 0){
                        if( $dayA[$i] == 0 ){
                            $lRow[$i]->workable == 0;    
                        }    
                    }

                    if($lRows[$i]->workable == 1){
                        if($lRows[$i]->isDayRepeated == false){
                            //checar si tiene ajuste
                            if(count($lRows[$i]->adjusts) != 0){
                                for($x = 0 ; count($lRows[$i]->adjusts) > $x ; $x++){
                                    if($lRows[$i]->adjusts[$x]->adjust_type_id == 4){
                                        $banderaAjuste = 1;
                                        $lRows[$i]->hasAdjust = true;
                                    }    
                                }
                            }
                            //caso sin checadas y sin ajuste
                            if( $lRows[$i]->hasChecks == false && $banderaAjuste == 0){
                                //si es ausencia
                                if( $lRows[$i]->hasAbsence == true ){
                                    $aAbsence[$contadorAusencia] = $i;
                                    $contadorAusencia++;
                                    //si es domingo y tiene ausencia se pone descanso y se prende bandera de que falta una ausencia en la semana
                                    if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                        $lRows[$i]->isDayOff = 1;
                                        $lRows[$i]->hasAbsence = false; 
                                        $haveDayoff = 1;  
                                        $missingAbsence = 1; 
                                    }
                                //si es dia festivo
                                }elseif( $lRows[$i]->isHoliday == true ){
                                    if($lRows[$i]->extraDoubleMins > 0){
                                        $aHoliday[$contadorHoliday] = $i;
                                        $contadorHoliday++;
                                    }
                                    //se comento como resultado de la reunión del 06 de mayo 2022 con SF
                                    /*
                                    if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                        $domingo = 1;
                                    }
                                    */
                                //si es descanso
                                }elseif( $lRows[$i]->isDayOff == 1){
                                    $haveDayoff = 1;
                                }
                                //si es un incidencia
                                else{
                                    if(sizeof($lRows[$i]->events)<1){
                                        //$lRows[$i]->hasAbsence = true;
                                        if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                            $lRows[$i]->isDayOff = 1; 
                                            $haveDayoff = 1; 
                                            $lRows[$i]->hasAbsence = false;  
                                            $missingAbsence = 1; 
                                        }
                                    }
                                    else {
                                        if($lRows[$i]->events[0]['type_id'] == 19){
                                            $lRows[$i]->isDayOff = 1; 
                                            $haveDayoff = 1;       
                                        }
                                        else {
                                            // Si el día de la incidencia es día domingo
                                            if (SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY) {
                                                $lEventsOfThisDay = prePayrollController::searchAbsenceByDay($lRows[$i]->idEmployee, $lRows[$i]->outDate);
                                                $isAllowed = true;
                                                // Si en las incidencias que tiene el día es una incidencia de tipo "No pagable"
                                                foreach($lEventsOfThisDay as $event) {
                                                    if (! $event->is_allowed) {
                                                        $isAllowed = false;
                                                        break;
                                                    }
                                                }

                                                // Si es "pagable" se agrega el descanso
                                                if ($isAllowed) {
                                                    $descansoImpl = 1;
                                                }
                                            }
                                        }
                                    }
                                }
                            //caso con checadas o con ajuste
                            }elseif( $lRows[$i]->hasChecks == true || $banderaAjuste == 1 || $lRows[$i]->isDayOff == 0 || $lRows[$i]->hasAbsence == 0 ){
                                if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                    //descanso implicito
                                    $descansoImpl = 1;
                                }
                                if( $lRows[$i]->extraDoubleMins == 0 && $lRows[$i]->extraTripleMins == 0 && $lRows[$i]->isSunday == 0){
                                    $aWithoutExtra[$contadorSinextra] = $i;
                                    $contadorSinextra++;
                                }else{
                                    $extraTotales = $lRows[$i]->extraDoubleMins +  $lRows[$i]->extraTripleMins;
                                    $posicionMenor[$contadorMenor] = $i;
                                    $horasExtraMenor[$contadorMenor] = $extraTotales;
                                    $contadorMenor++;
                                }
                                if( $lRows[$i]->isHoliday == true ){
                                    if($lRows[$i]->extraDoubleMins > 0){
                                        $aHoliday[$contadorHoliday] = $i;
                                        $contadorHoliday++;
                                    }
                                }else{
                                    $diasCorrectos[$auxCorrectos] = $i;
                                    $auxCorrectos++;
                                }
                                if(isset($lRows[$i]->events[0])){
                                    if($lRows[$i]->events[0]['type_id'] == 19){
                                        $lRows[$i]->isDayOff = 1; 
                                        $haveDayoff = 1;
                                        if($lRows[$i]->overtimeCheckPolicy != 1){
                                            $lRows[$i]->work_dayoff = 1;
                                        }       
                                    }
                                }
                            }
                        }
                    }else{
                        $lRows[$i]->isDayOff = 1;
                        $haveDayoff = 1;
                        if($lRows[$i]->hasChecks == 0){
                            $lRows[$i]->work_dayoff = 0;
                        }else{

                            if($lRows[$i]->overtimeCheckPolicy != 1){
                                $lRows[$i]->work_dayoff = 1;
                            } 
                        }
                    }
                }
                $diaSumar = 0;
                $posicionTransformar = 0;
                if(isset($diasCorrectos)){
                    // si falta día de descanso
                    if($haveDayoff == 0){
                        if($descansoImpl == 1){ 
                            // se pone en el Row 0 porque el conjunto debe ser igual.
                            if($lRows[0]->overtimeCheckPolicy != 1){
                                $lRows[0]->work_dayoff = 1;
                            }    
                        }
                        $concluir = 0;
                        if(isset($aWithoutExtra)){
                            for($i = 0 ; count($aWithoutExtra) > $i ; $i++){
                               if($aWithoutExtra[$i] == $diasOptimo){
                                    $lRows[$aWithoutExtra[$i]]->isDayOff = 1;
                                    if( $aWithoutExtra[$i] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                    $concluir = 1;
                                    $i = $i + 10;
                               } 
                            }
                            if($concluir != 1){
                                for($i = 0 ; count($aWithoutExtra) > $i ; $i++){
                                    for($j = 0 ; count($diasAntes) > $j ; $j++){
                                        if($aWithoutExtra[$i] == $diasAntes[$j]){
                                            $lRows[$aWithoutExtra[$i]]->isDayOff = 1;
                                            if( $aWithoutExtra[$i] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                            $concluir = 1;
                                            $i = $i + 10;
                                            $j = $j + 10;
                                       } 
                                    }
                                 }    
                            }
                            if($concluir != 1){
                                for($i = 0 ; count($aWithoutExtra) > $i ; $i++){
                                    for($j = 0 ; count($diasDespues) > $j ; $j++){
                                        if($aWithoutExtra[$i] == $diasDespues[$j]){
                                            $lRows[$aWithoutExtra[$i]]->isDayOff = 1;
                                            if( $aWithoutExtra[$i] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                            $concluir = 1;
                                            $i = $i + 10;
                                            $j = $j + 10;
                                       } 
                                    }
                                }
                            }
                        }
                        if(isset($posicionMenor) && $concluir == 0){
                            for($i = 0 ; count($posicionMenor) > $i ; $i++){
                                for($j = 0 ; count($diasDespues) > $j ; $j++){
                                    if($posicionMenor[$i] == $diasDespues[$j]){
                                        $lRows[$posicionMenor[$i]]->isDayOff = 1;
                                        $posicionTransformar = $posicionMenor[$i];
                                        if( $posicionMenor[$i] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                        $concluir = 1;
                                        $i = $i + 10;
                                        $j = $j + 10;
                                   } 
                                }
                            }
                            if($concluir != 1){
                                for($i = 0 ; count($posicionMenor) > $i ; $i++){
                                    for($j = 0 ; count($diasAntes) > $j ; $j++){
                                        if($posicionMenor[$i] == $diasAntes[$j]){
                                            $lRows[$posicionMenor[$i]]->isDayOff = 1;
                                            $posicionTransformar = $posicionMenor[$i];
                                            if( $posicionMenor[$i] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                            $concluir = 1;
                                            $i = $i + 10;
                                            $j = $j + 10;
                                       } 
                                    }
                                }
                            }
                            $realizar = 0;
                            if($diaSumar == 1){
                                if(count($diasCorrectos) > 1 ){
                                    $realizar = 1;
                                }
                            }else{
                                $realizar = 1;   
                            }
                            if($realizar == 1){
                                $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$posicionTransformar ]->extraDoubleMins;
                                $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$posicionTransformar ]->extraTripleMins;
                                
                                if($lRows[$posicionTransformar ]->extraDoubleMins > 0){
                                    if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                        $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                        $horas = intdiv($lRows[$posicionTransformar ]->extraDoubleMins,60);
                                        $minutos = $lRows[$posicionTransformar ]->extraDoubleMins % 60;

                                        $salidaMaquillada->addMinutes($minutos);
                                        $salidaMaquillada->addHours($horas);

                                        $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                        
                                    }else{
                                        $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                        $horas = intdiv($lRows[$posicionTransformar]->extraDoubleMins,60);
                                        $minutos = $lRows[$posicionTransformar ]->extraDoubleMins % 60;

                                        $salidaMaquillada->addMinutes($minutos);
                                        $salidaMaquillada->addHours($horas);

                                        $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                    }
                                    
                                }
                                $lRows[$posicionTransformar ]->extraDoubleMins = 0;
                                $lRows[$posicionTransformar]->extraTripleMins = 0;    
                            }
                        }
                    }
                    // si falta día de ausencia
                    if($missingAbsence == 1){
                        if(isset($aWithoutExtra)){
                            $lRows[$aWithoutExtra[0]]->hasAbsence = 1;
                            if( $aWithoutExtra[0] == $diasCorrectos[0] ){ $diaSumar = 1;}
                        }else{
                            $lRows[$posicionMenor[0]]->hasAbsence = 1;
                            if( $diasCorrectos[0] == $posicionMenor[0] ){
                                $diaSumar = 1;
                            }else{
                                $diaSumar = 0;
                            }
                            
                            $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$posicionMenor[0] ]->extraDoubleMins;
                            $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$posicionMenor[0] ]->extraTripleMins;
                            
                            if($lRows[$posicionMenor[0] ]->extraDoubleMins > 0){
                                if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                    $horas = intdiv($lRows[$posicionMenor[0] ]->extraDoubleMins,60);
                                    $minutos = $lRows[$posicionMenor[0]]->extraDoubleMins % 60;

                                    $salidaMaquillada->addMinutes($minutos);
                                    $salidaMaquillada->addHours($horas);

                                    $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                    
                                }else{
                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                    $horas = intdiv($lRows[$posicionMenor[0] ]->extraDoubleMins,60);
                                    $minutos = $lRows[$posicionMenor[0] ]->extraDoubleMins % 60;

                                    $salidaMaquillada->addMinutes($minutos);
                                    $salidaMaquillada->addHours($horas);

                                    $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                }
                                
                            }
                            $lRows[$posicionMenor[0] ]->extraDoubleMins = 0;
                            $lRows[$posicionMenor[0] ]->extraTripleMins = 0;    
                        }
                    }
                    if($contadorHoliday > 0){
                        for($i = 0 ; count($aHoliday) > $i ; $i++){
                            $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[ $aHoliday[$i] ]->extraDoubleMins;
                            $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[ $aHoliday[$i] ]->extraTripleMins;
                                
                            if($lRows[ $aHoliday[$i] ]->extraDoubleMins > 0){
                                if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                    $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                    $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
            
                                    $salidaMaquillada->addMinutes($minutos);
                                    $salidaMaquillada->addHours($horas);
            
                                    $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                        
                                }else{
                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                    $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                    $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
            
                                    $salidaMaquillada->addMinutes($minutos);
                                    $salidaMaquillada->addHours($horas);
            
                                    $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                }
                                    
                            }
                            $lRows[ $aHoliday[$i] ]->extraDoubleMins = 0;
                            $lRows[ $aHoliday[$i] ]->extraTripleMins = 0;    
                        }
                    } 
                }      
                break;                            
        }
        return $lRows; 
    }

      public static function restDayBi($lRows,$inicio,$fin,$semana,$year,$employee,$contador){
        $regla = 2;
        switch ($regla){

            case 1:
                $final = DB::table('week_cut')
                    ->where('id','=',$semana)
                    ->select('ini AS inicio', 'fin AS final')
                    ->get();
                $banderaAjuste = 0;
                $inicioContador = $contador[0];
                //conseguir la fecha de inicio
                if($lRows[$contador[0]]->inDate != null){
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->inDate);
                }else if($lRows[ $contador[0] ]->inDateTime != null){
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->inDateTime);    
                }else if($lRows[ $contador[0] ]->outDate != null){
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->outDate);
                }else{
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->outDateTime);
                }
                $chequeoPrimera = Carbon::parse($inicio);
                if($primeraFecha < $chequeoPrimera){ $primeraFecha->addDay();}
                $inicioSemana = Carbon::parse($final[0]->inicio);
                $noCompleto = 0;
                $diferencia = 0;
                $contadorRegistros = 0;
                $finalSemana = Carbon::parse($final[0]->final);
                if ( $primeraFecha > $inicioSemana ){
                    $diferencia = ($primeraFecha->diffInDays($inicioSemana));
                    for( $i = 0 ; $diferencia > $i ; $i++ ){
                        $fechas[$i] = $inicioSemana->toDateString();
                        $inicioSemana->addDay();
                    }
                    $diasTrabajados = DB::table('processed_data')
                            ->whereIn('inDate',$fechas)
                            ->where('employee_id',$employee)
                            ->select('haschecks AS checada','hasschedule AS programado','hasAdjust AS ajuste')
                            ->get();
                    
                    if(count($diasTrabajados) != 0 ){
                        for( $i = 0 ; count($diasTrabajados) > $i ; $i++ ){
                            if( $diasTrabajados[$i]->checada == 0 && $diasTrabajados[$i]->programado == 0 && $diasTrabajados[$i]->ajuste == 0){
                                $noCompleto = 1;
                            }
                        }
                    }else{
                        $noCompleto = 1;
                    } 
                    if ( $noCompleto == 1 ){
                        $aux = 1;
                        while( $aux == 1 ){
                            if($lRows[ $inicioContador ]->outDate != null){
                                $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDate);
                            }else{
                                $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDateTime);
                            }
                            if($finalSemana > $verificarFinsemana){
                                $contadorRegistros++;
                                $inicioContador++;
                            }else{
                                $aux = 0;
                            }

                        }
                        $contador[0] = $contadorRegistros+1;
                        $contador[1] = 0;
                        $contador[2] = 0;
                        return $contador;
                    }else{
                        $semanaNoCompleta = false;
                        $horaExtraMenor = 0;
                        $extraMenorPosicion = 0;
                        $posicionRango = 0;
                        $auxCorrectos = 0;
                        $contadorAusencia = 0;
                        $contadorSinextra = 0;
                        $contadorHoliday = 0;
                        $i = $contador[0];
                        $inicioContador = $contador[0];
                        $aux = 1;
                        $descansoImpl = 0;
                        while( $aux == 1 ){
                            if($lRows[ $inicioContador ]->outDate != null){
                                $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDate);
                            }else{
                                $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDateTime);
                            }
                            if($finalSemana > $verificarFinsemana){
                                $contadorRegistros++;
                                $inicioContador++;
                            }else{
                                $aux = 0;
                            }

                        }
                        $diferencia = $contadorRegistros + $i;
                        if($diferencia != 0){ 
                        for($i ; $diferencia >= $i ; $i++){
                            $dayA = [];
                            $dayA = days_works_array(1);
                            if($lRow[$i]->hasChecks == 0){
                                if( $dayA[$i] == 0 ){
                                    $lRow[$i]->workable == 0;    
                                }    
                            }
                            if($lRows[$i]->workable == 1){
                                if($lRows[$i]->isDayRepeated == false){
                                    if(count($lRows[$i]->adjusts) != 0){
                                        for($x = 0 ; count($lRows[$i]->adjusts) > $x ; $x++){
                                            if($lRows[$i]->adjusts[$x]->adjust_type_id == 4){
                                                $baderaAjuste = 1;
                                                $lRows[$i]->hasAdjust = true;
                                            }    
                                        }
                                    }
                                    if( $lRows[$i]->hasChecks == false && $banderaAjuste == 0 ){
                                        if( $lRows[$i]->hasAbsence == true ){
                                            $aAbsence[$contadorAusencia] = $i;
                                            $contadorAusencia++;
                                        }else{
                                            $semanaNoCompleta = true;
                                        }
                                        
                                    }elseif( $lRows[$i]->hasChecks == true || $banderaAjuste == 1){
                                        if( $lRows[$i]->extraDoubleMins == 0 && $lRows[$i]->extraTripleMins == 0 && $lRows[$i]->isSunday == 0){
                                            $aWithoutExtra[$contadorSinextra] = $i;
                                            $contadorSinextra++;
                                        }else{
                                            $extraTotales = $lRows[$i]->extraDoubleMins +  $lRows[$i]->extraTripleMins;
                                            if( ( $horaExtraMenor > $extraTotales || $horaExtraMenor == 0 ) && $lRows[$i]->isSunday == 0 ){
                                                $horaExtraMenor = $extraTotales;
                                                $extraMenorPosicion = $i;
                                            }
                                        }
                                        if( $lRows[$i]->isHoliday == true ){
                                            if($lRows[$i]->extraDoubleMins > 0){
                                                $aHoliday[$contadorHoliday] = $i;
                                                $contadorHoliday++;
                                            }
                                        }else{
                                            $diasCorrectos[$auxCorrectos] = $i;
                                            $auxCorrectos++;
                                        }
                                        
                                    }
                                }
                            }else{
                                $lRows[$i]->isDayOff = 1;
                                if($lRows[$i]->hasChecks == 0){
                                    $lRows[$i]->work_dayoff = 0;
                                }else{
                                    $lRows[$i]->work_dayoff = 1;
                                }
                                $semanaNoCompleta = 1;
                            }
                
                        }
                        $contador[0] = $i;
                        if($semanaNoCompleta != true){
                            if(isset($aAbsence)){
                                $lRows[$aAbsence[0]]->isDayOff = 1;
                                if($lRows[$aAbsence[0]]->hasChecks == 0){
                                    $lRows[$aAbsence[0]]->work_dayoff = 0;
                                }else{
                                    $lRows[$aAbsence[0]]->work_dayoff = 1;
                                }
                                $lRows[$aAbsence[0]]->hasAbsence = false;
                            }else{
                                    if(isset($aWithoutExtra)){
                                        $lRows[$aWithoutExtra[0]]->isDayOff = 1;
                                        if($lRows[$aWithoutExtra[0]]->hasChecks == 0){
                                            $lRows[$aWithoutExtra[0]]->work_dayoff = 0;
                                        }else{
                                            $lRows[$aWithoutExtra[0]]->work_dayoff = 1;
                                        }
                                        if( $aWithoutExtra[0] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                    }else{
                                        $lRows[$extraMenorPosicion]->isDayOff = 1;
                                        if($lRows[$extraMenorPosicion]->hasChecks == 0){
                                            $lRows[$extraMenorPosicion]->work_dayoff = 0;
                                        }else{
                                            $lRows[$extraMenorPosicion]->work_dayoff = 1;
                                        } 
                                        if( $diasCorrectos[0] == $extraMenorPosicion ){
                                            $diaSumar = 1;
                                        }else{
                                            $diaSumar = 0;
                                        }
                                        
                                        $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$extraMenorPosicion ]->extraDoubleMins;
                                        $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$extraMenorPosicion ]->extraTripleMins;
                                        
                                        if($lRows[$extraMenorPosicion ]->extraDoubleMins > 0){
                                            if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                                $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                                $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
                
                                                $salidaMaquillada->addMinutes($minutos);
                                                $salidaMaquillada->addHours($horas);
                
                                                $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                
                                            }else{
                                                $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                                $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
                
                                                $salidaMaquillada->addMinutes($minutos);
                                                $salidaMaquillada->addHours($horas);
                
                                                $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                            }
                                            
                                        }
                                        $lRows[$extraMenorPosicion ]->extraDoubleMins = 0;
                                        $lRows[$extraMenorPosicion ]->extraTripleMins = 0;
                                    }
                            }
                            if($contadorHoliday > 0){
                                for($i = 0 ; count($aHoliday) > $i ; $i++){
                                    $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[ $aHoliday[$i] ]->extraDoubleMins;
                                    $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[ $aHoliday[$i] ]->extraTripleMins;
                                    
                                    if($lRows[ $aHoliday[$i] ]->extraDoubleMins > 0){
                                        if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                            $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                            $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                            $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
                
                                            $salidaMaquillada->addMinutes($minutos);
                                            $salidaMaquillada->addHours($horas);
                
                                            $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                            
                                        }else{
                                            $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                            $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                            $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
                
                                            $salidaMaquillada->addMinutes($minutos);
                                            $salidaMaquillada->addHours($horas);
                
                                            $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                        }
                                        
                                    }
                                    $lRows[ $aHoliday[$i] ]->extraDoubleMins = 0;
                                    $lRows[ $aHoliday[$i] ]->extraTripleMins = 0;    
                                }
                            }
                
                                
                        }
                        $contador[1] = 0;
                        $contador[2] = 0;
                        return $contador;
                        }else{
                            $lRows[0]->isDayOff = 1 ;
                            $contador[0] = 1;
                            $contador[1] = $lRows[0]->extraDoubleMins;
                            $contador[2] = $lRows[0]->extraTripleMins;
                            return $contador;
                        }

                    }
                }else if ($primeraFecha <= $inicioSemana){
                        if( $contador[1] != 0 || $contador[2] != 0){
                            $lRows [$contador[0]]->extraDoubleMins = $lRows [$contador[0]]->extraDoubleMins + $contador[1];
                            $lRows [$contador[0]]->extraTripleMins = $lRows [$contador[0]]->extraTripleMins + $contador[2]; 
                        
                            if( $lRows[ $contador[0] ]->outDateTimeNoficial != null ){
                                $salidaMaquillada = Carbon::parse($lRows[ $contador[0] ]->outDateTimeNoficial);
                                $horas = intdiv($contador[1],60);
                                $minutos = $contador[2] % 60;

                                $salidaMaquillada->addMinutes($minutos);
                                $salidaMaquillada->addHours($horas);

                                $lRows[ $contador[0] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                
                            }else{
                                $salidaMaquillada = Carbon::parse($lRows[ $contador[0] ]->outDateTime );
                                $horas = intdiv($contador[1],60);
                                $minutos = $contador[2] % 60;

                                $salidaMaquillada->addMinutes($minutos);
                                $salidaMaquillada->addHours($horas);

                                $lRows[$contador[0]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                            }
                        }
                        $semanaNoCompleta = false;
                        $horaExtraMenor = 0;
                        $extraMenorPosicion = 0;
                        $posicionRango = 0;
                        $auxCorrectos = 0;
                        $contadorAusencia = 0;
                        $contadorSinextra = 0;
                        //$i = $contador[0];
                        //$diferencia = 7 + $contador[0];
                        $i = $contador[0];
                        $inicioContador = $contador[0];
                        $aux = 1;
                        $finSemanaPrematura = 0;
                        $numeroRegistros = count($lRows);
                        while( $aux == 1 ){
                            if($inicioContador < count($lRows)){
                                if($lRows[ $inicioContador ]->outDate != null){
                                    $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDate);
                                }else{
                                    $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDateTime);
                                }
                                if($finalSemana >= $verificarFinsemana){
                                    $contadorRegistros++;
                                    $inicioContador++;
                                }else{
                                    $aux = 0;
                                }
                            }else{
                                $finSemanaPrematura = 1;
                                $aux = 0;
                            }
                        }
                        $diferencia = $contadorRegistros + $i;
                        $days = 0;
                        if ( $finSemanaPrematura == 0 ){
                            for($i ; $diferencia > $i ; $i++){
                                if($lRows[$i]->workable == 1){
                                if($lRows[$i]->isDayRepeated == false){
                                    if(count($lRows[$i]->adjusts) != 0){
                                        for($x = 0 ; count($lRows[$i]->adjusts) > $x ; $x++){
                                            if($lRows[$i]->adjusts[$x]->adjust_type_id == 4){
                                                $baderaAjuste = 1;
                                                $lRows[$i]->hasAdjust = true;
                                            }    
                                        }
                                    }
                                if( $lRows[$i]->hasChecks == false && $banderaAjuste == 0 ){
                                    if( $lRows[$i]->hasAbsence == true ){
                                        $aAbsence[$contadorAusencia] = $i;
                                        $contadorAusencia++;
                                    }else{
                                        $semanaNoCompleta = true;
                                    }
                                    
                                }elseif( $lRows[$i]->hasChecks == true || $banderaAjuste == 1 ){
                                    if( $lRows[$i]->extraDoubleMins == 0 && $lRows[$i]->extraTripleMins == 0  && $lRows[$i]->isSunday == 0){
                                        $aWithoutExtra[$contadorSinextra] = $i;
                                        $contadorSinextra++;
                                    }else{
                                        $extraTotales = $lRows[$i]->extraDoubleMins + $lRows[$i]->extraTripleMins;
                                        if( ( $horaExtraMenor > $extraTotales || $horaExtraMenor == 0 ) && $lRows[$i]->isSunday == 0 ){
                                            $horaExtraMenor = $extraTotales;
                                            $extraMenorPosicion = $i;
                                        }
                                    }
                                    $diasCorrectos[$auxCorrectos] = $i;
                                    $auxCorrectos++;
                                }
                                }
                                }else{
                                    $lRows[$i]->isDayOff = 1;
                                    if($lRows[$i]->hasChecks == 0){
                                        $lRows[$i]->work_dayoff = 0;
                                    }else{
                                        $lRows[$i]->work_dayoff = 1;
                                    }
                                    $semanaNoCompleta = 1;
                                }
                            }
                            $contador[0] = $i;
                            $contador[1] = 0;
                            $contador[2] = 0;
                            if($semanaNoCompleta != true){
                                if(isset($aAbsence)){
                                    $lRows[$aAbsence[0]]->isDayOff = 1;
                                    if($lRows[$aAbsence[0]]->hasChecks == 0){
                                        $lRows[$aAbsence[0]]->work_dayoff = 0;
                                    }else{
                                        $lRows[$aAbsence[0]]->work_dayoff = 1;
                                    }
                                    $lRows[$aAbsence[0]]->hasAbsence = false;
                                }else{
                                    $lRows[$extraMenorPosicion]->isDayOff = 1; 
                                    
                                    if($lRows[$extraMenorPosicion]->hasChecks == 0){
                                        $lRows[$extraMenorPosicion]->work_dayoff = 0;
                                    }else{
                                        $lRows[$extraMenorPosicion]->work_dayoff = 1;
                                    }
                                    if( $diasCorrectos[0] == $extraMenorPosicion ){
                                        $diaSumar = 1;
                                    }else{
                                        $diaSumar = 0;
                                    }
                                    
                                    $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$extraMenorPosicion ]->extraDoubleMins;
                                    $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$extraMenorPosicion ]->extraTripleMins;
                                    
                                    if($lRows[$extraMenorPosicion ]->extraDoubleMins > 0){
                                        if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                            $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                            $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                            $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
            
                                            $salidaMaquillada->addMinutes($minutos);
                                            $salidaMaquillada->addHours($horas);
            
                                            $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                            
                                        }else{
                                            $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                            $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                            $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
            
                                            $salidaMaquillada->addMinutes($minutos);
                                            $salidaMaquillada->addHours($horas);
            
                                            $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                        }
                                        
                                    }
                                    $lRows[$extraMenorPosicion ]->extraDoubleMins = 0;
                                    $lRows[$extraMenorPosicion ]->extraTripleMins = 0;
                                }
                                    
                            }
            
                            return $contador;
                    }else{
                        $contador[0] = 0;
                        $contador[1] = 0;
                        $contador[2] = 0;
                        return $contador; 
                    }
                    
                }
                break;
            case 2:
                $final = DB::table('week_cut')
                    ->where('id','=',$semana)
                    ->select('ini AS inicio', 'fin AS final')
                    ->get();
                $banderaAjuste = 0;
                $inicioContador = $contador[0];
                //conseguir la fecha de inicio
                if($lRows[$contador[0]]->inDate != null){
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->inDate);
                }else if($lRows[ $contador[0] ]->inDateTime != null){
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->inDateTime);    
                }else if($lRows[ $contador[0] ]->outDate != null){
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->outDate);
                }else{
                    $primeraFecha = Carbon::parse($lRows[ $contador[0] ]->outDateTime);
                }
                $chequeoPrimera = Carbon::parse($inicio);
                if($primeraFecha < $chequeoPrimera){ $primeraFecha->addDay();}
                $inicioSemana = Carbon::parse($final[0]->inicio);
                $noCompleto = 0;
                $diferencia = 0;
                $contadorRegistros = 0;
                $haveDayoff = 0;
                $finalSemana = Carbon::parse($final[0]->final);
                // Si la primera fecha que se envia es superior al inicio de la semana, es una semana incompleta, que una parte ya debe estar procesada
                if ( $primeraFecha > $inicioSemana ){
                    //se sacan los días de la semana que se debieron procesar con anterioridad
                    $diferencia = ($primeraFecha->diffInDays($inicioSemana));
                    for( $i = 0 ; $diferencia > $i ; $i++ ){
                        $fechas[$i] = $inicioSemana->toDateString();
                        $inicioSemana->addDay();
                    }
                    $diasTrabajados = DB::table('processed_data')
                            ->whereIn('inDate',$fechas)
                            ->where('employee_id',$employee)
                            ->select('is_sunday AS domingo','is_dayoff AS descanso','hasabsence AS falta','haschecks AS checadas')
                            ->get();
                    
                    if(count($diasTrabajados) != 0 ){
                        for( $i = 0 ; count($diasTrabajados) > $i ; $i++ ){
                            //checar si ya tiene un descanso asignado
                            if( $diasTrabajados[$i]->descanso == 1){
                                $haveDayoff = 1;
                                if ($diasTrabajados[$i]->checadas == 1){
                                    //$lRows[$i]->work_dayoff = 1;
                                }
                            }   
                        }
                    } 
                    $semanaNoCompleta = false;
                    $horaExtraMenor = 0;
                    $extraMenorPosicion = 0;
                    $posicionRango = 0;
                    $auxCorrectos = 0;
                    $contadorAusencia = 0;
                    $contadorSinextra = 0;
                    $contadorHoliday = 0;
                    $i = $contador[0];
                    $inicioContador = $contador[0];
                    $aux = 1;
                    $descansoImpl = 0;
                    while( $aux == 1 ){
                        if($lRows[ $inicioContador ]->outDate != null){
                            $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDate);
                        }else{
                            $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDateTime);
                        }
                        if($finalSemana > $verificarFinsemana){
                            $contadorRegistros++;
                            $inicioContador++;
                        }else{
                            $aux = 0;
                        }
                    }
                    $diferencia = $contadorRegistros + $i;
                    if($diferencia != 0){ 
                        for($i ; $diferencia >= $i ; $i++){
                            if($lRows[$i]->workable == 1){
                                if($lRows[$i]->isDayRepeated == false){
                                    //checar si tiene ajuste
                                    if(count($lRows[$i]->adjusts) != 0){
                                        for($x = 0 ; count($lRows[$i]->adjusts) > $x ; $x++){
                                            if($lRows[$i]->adjusts[$x]->adjust_type_id == 4){
                                                $banderaAjuste = 1;
                                                $lRows[$i]->hasAdjust = true;
                                            }    
                                        }
                                    }
                                    //caso sin checadas y sin ajuste
                                    if( $lRows[$i]->hasChecks == false && $banderaAjuste == 0){
                                        //si es ausencia
                                        if( $lRows[$i]->hasAbsence == true ){
                                            $aAbsence[$contadorAusencia] = $i;
                                            $contadorAusencia++;
                                            //si es domingo y tiene ausencia se pone descanso y se prende bandera de que falta una ausencia en la semana
                                            if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                $lRows[$i]->isDayOff = 1; 
                                                $haveDayoff = 1;  
                                                $missingAbsence = 1; 
                                            }
                                        //si es dia festivo
                                        }elseif( $lRows[$i]->isHoliday == true ){
                                            if($lRows[$i]->extraDoubleMins > 0){
                                                $aHoliday[$contadorHoliday] = $i;
                                                $contadorHoliday++;
                                            }
                                            /*
                                            if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                $domingo = 1;
                                            }
                                            */
                                        //si es descanso
                                        }elseif( $lRows[$i]->isDayOff == 1){
                                            $haveDayoff = 1;
                                        }
                                        //si es un incidencia
                                        else{
                                            if(sizeof($lRows[$i]->events)<1){
                                                //$lRows[$i]->hasAbsence = true;
                                                if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                    $lRows[$i]->isDayOff = 1; 
                                                    $haveDayoff = 1;  
                                                    $missingAbsence = 1; 
                                                }
                                            }else{
                                                if($lRows[$i]->events[0]['type_id'] == 19){
                                                    $lRows[$i]->isDayOff = 1; 
                                                    $haveDayoff = 1;       
                                                }else{
                                                    if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                        $descansoImpl = 1;
                                                    }   
                                                }
                                            }
                                        }
                                    //caso con checadas o con ajuste
                                    }elseif( $lRows[$i]->hasChecks == true || $banderaAjuste == 1){
                                        if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                            $descansoImpl = 1;
                                        }
                                        if( $lRows[$i]->extraDoubleMins == 0 && $lRows[$i]->extraTripleMins == 0 && $lRows[$i]->isSunday == 0){
                                            $aWithoutExtra[$contadorSinextra] = $i;
                                            $contadorSinextra++;
                                        }else{
                                            $extraTotales = $lRows[$i]->extraDoubleMins +  $lRows[$i]->extraTripleMins;
                                            if( ( $horaExtraMenor > $extraTotales || $horaExtraMenor == 0 ) && $lRows[$i]->isSunday == 0 ){
                                                $horaExtraMenor = $extraTotales;
                                                $extraMenorPosicion = $i;
                                            }
                                        }
                                        if( $lRows[$i]->isHoliday == true ){
                                            if($lRows[$i]->extraDoubleMins > 0){
                                                $aHoliday[$contadorHoliday] = $i;
                                                $contadorHoliday++;
                                            }
                                        }else{
                                            $diasCorrectos[$auxCorrectos] = $i;
                                            $auxCorrectos++;
                                        }
                                        if( sizeof($lRows[$i]->events) >= 1 ){
                                            if( $lRows[$i]->events[0]['type_id'] == 19 ){
                                                $lRows[$i]->isDayOff = 1; 
                                                $haveDayoff = 1;       
                                            }else{
                                                if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                    $descansoImpl = 1;
                                                }   
                                            }
                                        }
                                        
                                    }
                                }
                            }else{
                                $lRows[$i]->isDayOff = 1;
                                $haveDayoff = 1;
                                if($lRows[$i]->hasChecks == 0){
                                    $lRows[$i]->work_dayoff = 0;
                                }else{
                                    if($lRows[$i]->overtimeCheckPolicy != 1){
                                        $lRows[$i]->work_dayoff = 1;
                                    }else{
                                        $lRows[$i]->work_dayoff = 0;   
                                    } 
                                }
                            }
                
                        }
                        $contador[0] = $i;
                        $diaSumar = 0;
                        if(isset($diasCorrectos)){
                            if($haveDayoff == 0){
                                if($descansoImpl == 1){ 
                                    if($lRows[$i]->overtimeCheckPolicy != 1){
                                        $lRows[$i]->work_dayoff = 1;
                                    }else{
                                        $lRows[$i]->work_dayoff = 0;   
                                    }    
                                }
                                if(isset($aWithoutExtra)){
                                    $lRows[$aWithoutExtra[0]]->isDayOff = 1;
                                    if( $aWithoutExtra[0] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                }else{
                                    $lRows[$extraMenorPosicion]->isDayOff = 1;
                                    if( $diasCorrectos[0] == $extraMenorPosicion ){
                                        $diaSumar = 1;
                                    }else{
                                        $diaSumar = 0;
                                    }
                                    $realizar = 0;
                                    if($diaSumar == 1){
                                        if(count($diasCorrectos) > 1 ){
                                            $realizar = 1;
                                        }
                                    }else{
                                        $realizar = 1;   
                                    }
                                    if($realizar == 1){
                                        $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$extraMenorPosicion ]->extraDoubleMins;
                                        $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$extraMenorPosicion ]->extraTripleMins;
                                        
                                        if($lRows[$extraMenorPosicion ]->extraDoubleMins > 0){
                                            if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                                $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                                $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
                
                                                $salidaMaquillada->addMinutes($minutos);
                                                $salidaMaquillada->addHours($horas);
                
                                                $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                
                                            }else{
                                                $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                                $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
                
                                                $salidaMaquillada->addMinutes($minutos);
                                                $salidaMaquillada->addHours($horas);
                
                                                $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                            }
                                            
                                        }
                                        $lRows[$extraMenorPosicion ]->extraDoubleMins = 0;
                                        $lRows[$extraMenorPosicion ]->extraTripleMins = 0;  
                                    }   
                                }
                            }
                            if($contadorHoliday > 0){
                                for($i = 0 ; count($aHoliday) > $i ; $i++){
                                    $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[ $aHoliday[$i] ]->extraDoubleMins;
                                    $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[ $aHoliday[$i] ]->extraTripleMins;
                                        
                                    if($lRows[ $aHoliday[$i] ]->extraDoubleMins > 0){
                                        if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                            $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                            $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                            $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
                    
                                            $salidaMaquillada->addMinutes($minutos);
                                            $salidaMaquillada->addHours($horas);
                    
                                            $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                
                                        }else{
                                            $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                            $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                            $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
                    
                                            $salidaMaquillada->addMinutes($minutos);
                                            $salidaMaquillada->addHours($horas);
                    
                                            $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                        }
                                            
                                    }
                                    $lRows[ $aHoliday[$i] ]->extraDoubleMins = 0;
                                    $lRows[ $aHoliday[$i] ]->extraTripleMins = 0;    
                                }
                            }
                        }  
                        $contador[1] = 0;
                        $contador[2] = 0;
                        return $contador;
                    }else{
                        if( $haveDayoff == 0){
                            $lRows[0]->isDayOff = 1 ;
                            if($lRows[0]->overtimeCheckPolicy != 1){
                                $lRows[0]->work_dayoff = 1;
                            }else{
                                $lRows[$i]->work_dayoff = 0;   
                            }  
                            
                            $contador[0] = 1;
                            $contador[1] = $lRows[0]->extraDoubleMins;
                            $contador[2] = $lRows[0]->extraTripleMins;
                        }
                        
                        return $contador;
                    }
                // Cuando no es la primera semana
                }elseif ($primeraFecha <= $inicioSemana){
                        if( $contador[1] != 0 || $contador[2] != 0){
                            $lRows [$contador[0]]->extraDoubleMins = $lRows [$contador[0]]->extraDoubleMins + $contador[1];
                            $lRows [$contador[0]]->extraTripleMins = $lRows [$contador[0]]->extraTripleMins + $contador[2]; 
                        
                            if( $lRows[ $contador[0] ]->outDateTimeNoficial != null ){
                                $salidaMaquillada = Carbon::parse($lRows[ $contador[0] ]->outDateTimeNoficial);
                                $horas = intdiv($contador[1],60);
                                $minutos = $contador[2] % 60;

                                $salidaMaquillada->addMinutes($minutos);
                                $salidaMaquillada->addHours($horas);

                                $lRows[ $contador[0] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                
                            }else{
                                $salidaMaquillada = Carbon::parse($lRows[ $contador[0] ]->outDateTime );
                                $horas = intdiv($contador[1],60);
                                $minutos = $contador[2] % 60;

                                $salidaMaquillada->addMinutes($minutos);
                                $salidaMaquillada->addHours($horas);

                                $lRows[$contador[0]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                            }
                        }
                        $semanaNoCompleta = false;
                        $horaExtraMenor = 0;
                        $extraMenorPosicion = 0;
                        $posicionRango = 0;
                        $auxCorrectos = 0;
                        $contadorAusencia = 0;
                        $contadorSinextra = 0;
                        //$i = $contador[0];
                        //$diferencia = 7 + $contador[0];
                        $i = $contador[0];
                        $inicioContador = $contador[0];
                        $aux = 1;
                        $finSemanaPrematura = 0;
                        $numeroRegistros = count($lRows);
                        $descansoImpl = 0;
                        $haveDayoff = 0;
                        $missingAbsence = 0;
                        $contadorHoliday = 0;
                        while( $aux == 1 ){
                            if($inicioContador < count($lRows)){
                                if($lRows[ $inicioContador ]->outDate != null){
                                    $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDate);
                                }else{
                                    $verificarFinsemana = Carbon::parse($lRows[ $inicioContador ]->outDateTime);
                                }
                                if($finalSemana >= $verificarFinsemana){
                                    $contadorRegistros++;
                                    $inicioContador++;
                                }else{
                                    $aux = 0;
                                }
                            }else{
                                $finSemanaPrematura = 1;
                                $aux = 0;
                            }
                        }
                        $diferencia = $contadorRegistros + $i;
                        $days = 0;
                        if ( $finSemanaPrematura == 0 ){
                            if($diferencia != 0){ 
                                for($i ; $diferencia > $i ; $i++){
                                    if($lRows[$i]->workable == 1){
                                        if($lRows[$i]->isDayRepeated == false){
                                            //checar si tiene ajuste
                                            if(count($lRows[$i]->adjusts) != 0){
                                                for($x = 0 ; count($lRows[$i]->adjusts) > $x ; $x++){
                                                    if($lRows[$i]->adjusts[$x]->adjust_type_id == 4){
                                                        $banderaAjuste = 1;
                                                        $lRows[$i]->hasAdjust = true;
                                                    }    
                                                }
                                            }
                                            //caso sin checadas y sin ajuste
                                            if( $lRows[$i]->hasChecks == false && $banderaAjuste == 0){
                                                //si es ausencia
                                                if( $lRows[$i]->hasAbsence == true ){
                                                    $aAbsence[$contadorAusencia] = $i;
                                                    $contadorAusencia++;
                                                    //si es domingo y tiene ausencia se pone descanso y se prende bandera de que falta una ausencia en la semana
                                                    if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                        $lRows[$i]->isDayOff = 1; 
                                                        $haveDayoff = 1;  
                                                        $missingAbsence = 1; 
                                                    }
                                                //si es dia festivo
                                                }elseif( $lRows[$i]->isHoliday == true ){
                                                    if($lRows[$i]->extraDoubleMins > 0){
                                                        $aHoliday[$contadorHoliday] = $i;
                                                        $contadorHoliday++;
                                                    }
                                                    /*
                                                    if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                        $domingo = 1;
                                                    }
                                                    */
                                                //si es descanso
                                                }elseif( $lRows[$i]->isDayOff == 1){
                                                    $haveDayoff = 1;
                                                }
                                                //si es un incidencia
                                                else{
                                                    if(sizeof($lRows[$i]->events)<1){
                                                        //$lRows[$i]->hasAbsence = true;
                                                        if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                            $lRows[$i]->isDayOff = 1; 
                                                            $haveDayoff = 1;  
                                                            $missingAbsence = 1; 
                                                        }
                                                    }else{
                                                        if($lRows[$i]->events[0]['type_id'] == 19){
                                                            $lRows[$i]->isDayOff = 1; 
                                                            $haveDayoff = 1;       
                                                        }else{
                                                            if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                                $descansoImpl = 1;
                                                            }
                                                        }
                                                    }
                                                }
                                            //caso con checadas o con ajuste
                                            }elseif( $lRows[$i]->hasChecks == true || $banderaAjuste == 1){
                                                if(SDateTimeUtils::dayOfWeek($lRows[$i]->outDate) == Carbon::SUNDAY){
                                                    $descansoImpl = 1;
                                                }
                                                if( $lRows[$i]->extraDoubleMins == 0 && $lRows[$i]->extraTripleMins == 0 && $lRows[$i]->isSunday == 0){
                                                    $aWithoutExtra[$contadorSinextra] = $i;
                                                    $contadorSinextra++;
                                                }else{
                                                    $extraTotales = $lRows[$i]->extraDoubleMins +  $lRows[$i]->extraTripleMins;
                                                    if( ( $horaExtraMenor > $extraTotales || $horaExtraMenor == 0 ) && $lRows[$i]->isSunday == 0 ){
                                                        $horaExtraMenor = $extraTotales;
                                                        $extraMenorPosicion = $i;
                                                    }
                                                }
                                                if( $lRows[$i]->isHoliday == true ){
                                                    if($lRows[$i]->extraDoubleMins > 0){
                                                        $aHoliday[$contadorHoliday] = $i;
                                                        $contadorHoliday++;
                                                    }
                                                }else{
                                                    $diasCorrectos[$auxCorrectos] = $i;
                                                    $auxCorrectos++;
                                                }
                                                if(isset($lRows[$i]->events[0])){
                                                    if($lRows[$i]->events[0]['type_id'] == 19){
                                                        $lRows[$i]->isDayOff = 1; 
                                                        $haveDayoff = 1;
                                                        if($lRows[$i]->overtimeCheckPolicy != 1){
                                                            $lRows[$i]->work_dayoff = 1;
                                                        }else{
                                                            $lRows[$i]->work_dayoff = 0;   
                                                        }          
                                                    }
                                                }
                                                
                                            }
                                        }
                                    }else{
                                        $lRows[$i]->isDayOff = 1;
                                        $haveDayoff = 1;
                                        if($lRows[$i]->hasChecks == 0){
                                            $lRows[$i]->work_dayoff = 0;
                                        }else{
                                            if($lRows[$i]->overtimeCheckPolicy != 1){
                                                $lRows[$i]->work_dayoff = 1;
                                            }else{
                                                $lRows[$i]->work_dayoff = 0;   
                                            }   
                                        }
                                    }
                        
                                }
                                $contador[0] = $i;
                                $diaSumar = 0;
                                if(isset($diasCorrectos)){
                                    if($haveDayoff == 0){
                                        if($descansoImpl == 1){ if($lRows[$i-1]->overtimeCheckPolicy != 1){
                                            $lRows[$i]->work_dayoff = 1;
                                            }else{
                                                $lRows[$i]->work_dayoff = 0;   
                                            }    
                                        }
                                        if(isset($aWithoutExtra)){
                                            $lRows[$aWithoutExtra[0]]->isDayOff = 1;
                                            if( $aWithoutExtra[0] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                        }else{
                                            $lRows[$extraMenorPosicion]->isDayOff = 1;
                                            if( $diasCorrectos[0] == $extraMenorPosicion ){
                                                $diaSumar = 1;
                                            }else{
                                                $diaSumar = 0;
                                            }
                                            
                                            $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$extraMenorPosicion ]->extraDoubleMins;
                                            $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$extraMenorPosicion ]->extraTripleMins;
                                            
                                            if($lRows[$extraMenorPosicion ]->extraDoubleMins > 0){
                                                if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                                    $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                    $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
                    
                                                    $salidaMaquillada->addMinutes($minutos);
                                                    $salidaMaquillada->addHours($horas);
                    
                                                    $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                    
                                                }else{
                                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                                    $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                    $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;
                    
                                                    $salidaMaquillada->addMinutes($minutos);
                                                    $salidaMaquillada->addHours($horas);
                    
                                                    $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                }
                                                
                                            }
                                            $lRows[$extraMenorPosicion ]->extraDoubleMins = 0;
                                            $lRows[$extraMenorPosicion ]->extraTripleMins = 0;    
                                        }
                                    }
                                    // si falta día de ausencia
                                    if($missingAbsence == 1){
                                        if(isset($aWithoutExtra)){
                                            $lRows[$aWithoutExtra[0]]->hasAbsence = 1;
                                            if( $aWithoutExtra[0] == $diasCorrectos[0] ){ $diaSumar = 1;}
                                        }else{
                                            $lRows[$extraMenorPosicion]->hasAbsence = 1;
                                            if( $diasCorrectos[0] == $extraMenorPosicion ){
                                                $diaSumar = 1;
                                            }else{
                                                $diaSumar = 0;
                                            }
                                            
                                            $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[$extraMenorPosicion ]->extraDoubleMins;
                                            $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[$extraMenorPosicion ]->extraTripleMins;
                                            
                                            if($lRows[$extraMenorPosicion ]->extraDoubleMins > 0){
                                                if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                                    $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                    $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;

                                                    $salidaMaquillada->addMinutes($minutos);
                                                    $salidaMaquillada->addHours($horas);

                                                    $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                    
                                                }else{
                                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                                    $horas = intdiv($lRows[$extraMenorPosicion ]->extraDoubleMins,60);
                                                    $minutos = $lRows[$extraMenorPosicion ]->extraDoubleMins % 60;

                                                    $salidaMaquillada->addMinutes($minutos);
                                                    $salidaMaquillada->addHours($horas);

                                                    $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                }
                                                
                                            }
                                            $lRows[$extraMenorPosicion ]->extraDoubleMins = 0;
                                            $lRows[$extraMenorPosicion ]->extraTripleMins = 0;    
                                        }
                                    }
                                    if($contadorHoliday > 0){
                                        for($i = 0 ; count($aHoliday) > $i ; $i++){
                                            $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraDoubleMins + $lRows[ $aHoliday[$i] ]->extraDoubleMins;
                                            $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins = $lRows[ $diasCorrectos[$diaSumar] ]->extraTripleMins + $lRows[ $aHoliday[$i] ]->extraTripleMins;
                                                
                                            if($lRows[ $aHoliday[$i] ]->extraDoubleMins > 0){
                                                if( $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial != null ){
                                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial);
                                                    $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                                    $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
                            
                                                    $salidaMaquillada->addMinutes($minutos);
                                                    $salidaMaquillada->addHours($horas);
                            
                                                    $lRows[ $diasCorrectos[$diaSumar] ]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                        
                                                }else{
                                                    $salidaMaquillada = Carbon::parse($lRows[ $diasCorrectos[$diaSumar] ]->outDateTime );
                                                    $horas = intdiv($lRows[ $aHoliday[$i] ]->extraDoubleMins,60);
                                                    $minutos = $lRows[ $aHoliday[$i] ]->extraDoubleMins % 60;
                            
                                                    $salidaMaquillada->addMinutes($minutos);
                                                    $salidaMaquillada->addHours($horas);
                            
                                                    $lRows[$diasCorrectos[$diaSumar]]->outDateTimeNoficial = $salidaMaquillada->toDateTimeString();
                                                }
                                                    
                                            }
                                            $lRows[ $aHoliday[$i] ]->extraDoubleMins = 0;
                                            $lRows[ $aHoliday[$i] ]->extraTripleMins = 0;    
                                        }
                                    }  
                                }
                                $contador[1] = 0;
                                $contador[2] = 0;
                                return $contador;
                            }else{
                                $lRows[0]->isDayOff = 1 ;
                                $contador[0] = 1;
                                $contador[1] = $lRows[0]->extraDoubleMins;
                                $contador[2] = $lRows[0]->extraTripleMins;
                                return $contador;
                            }
            
                            return $contador;
                        }else{
                            $contador[0] = 0;
                            $contador[1] = 0;
                            $contador[2] = 0;

                            for($i ; $diferencia > $i ; $i++){

                                if(isset($lRows[$i]->events[0])){
                                    if($lRows[$i]->events[0]['type_id'] == 19){
                                        $lRows[$i]->isDayOff = 1; 
                                        $haveDayoff = 1;
                                        if($lRows[$i]->overtimeCheckPolicy != 1){
                                            $lRows[$i]->work_dayoff = 1;
                                        }else{
                                            $lRows[$i]->work_dayoff = 0;   
                                        }         
                                    }
                                }
                            
                            }

                            return $contador; 
                        }
                    
                }
                break;  
        }  
    } 
      
      public static function preProcessInfo($iIni = '', $iYear = 0, $iFin = '',$sTypePay = 0,$force = 0){
        //primero del rango completo se sacan las semanas o quincenas que a
        $semanas = SDateUtils::getNumWeek($iIni,$iYear,$iFin,$sTypePay);
        if($semanas[0] != 0){
            $pendientesProcesar = SDateUtils::isProcessed($semanas,$iYear,$sTypePay);
            //$pendientesProcesar = SPrepayrollStatus::getStatus($semanas,$iYear,$sTypePay,$force);
        }else{
            $pendientesProcesar[0] = 0;
        }
        
        if($pendientesProcesar[0] != 0){
        switch($sTypePay){
             case 2:
                $empleados = DB::table('employees')
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',2)
                                //->where('id',1424)
                                ->orderBy('id')
                                ->select('id AS id','policy_extratime_id AS extratime')
                                ->get();
                $num_empleados = count($empleados);
                $semanas = DB::table('week_cut')
                                ->whereIn('num',$pendientesProcesar)
                                ->where('year',$iYear)
                                ->select('ini AS inicio','fin AS final','id AS id')
                                ->get();
                $num_semanas = count($semanas);

                for ( $i = 0 ; $num_semanas > $i ; $i++ ){
                    $cerrado = 1;
                    for( $j = 0 ; $num_empleados > $j ; $j++ ){
                        $inicio = Carbon::parse($semanas[$i]->inicio);
                        $fin = Carbon::parse($semanas[$i]->final);
                        $diferencia = ($inicio->diffInDays($fin))+1;
                        $empl[0] = $empleados[$j]->id;
                        $extratime = $empleados[$j]->extratime;
                        $lRows = SInfoWithPolicy::standardization($semanas[$i]->inicio,$semanas[$i]->final,$sTypePay,$empl);
                        $lRows = SInfoWithPolicy::handlingHours($lRows,$diferencia,$extratime);
                        $lRows = SInfoWithPolicy::restDay($lRows,$diferencia);

                        SInfoWithPolicy::guardarProcesamiento($empleados[$j]->id,$lRows,$semanas[$i]->id,$iYear,2);
                        
                    }

                    SInfoWithPolicy::closePeriod( $semanas[$i]->id, $sTypePay, $inicio->year);
                        
                }
            break;
            case 1:
                $contador[0] = 0;
                $contador[1] = 0;
                $contador[2] = 0;
                $config = \App\SUtils\SConfiguration::getConfigurations();
                $empleados = DB::table('employees')
                                ->where('is_active','=',1)
                                ->where('way_pay_id','=',1)
                                //->where('id',1245)
                                ->where('department_id','!=',$config->dept_foraneo)
                                ->orderBy('id')
                                ->select('id AS id','policy_extratime_id AS extratime')
                                ->get();
                $num_empleados = count($empleados);
                $quincenas = DB::table('hrs_prepay_cut')
                                ->whereIn('id',$pendientesProcesar)
                                ->select('dt_cut AS cut', 'num AS num', 'year AS year','id AS id')
                                ->get();
                $num_semanas = count($quincenas);
                for( $j = 0 ; count($quincenas) > $j ; $j++ ){                
                    if($quincenas[$j]->num > 1){
                        $inicioAux = DB::table('hrs_prepay_cut')
                        ->where('year', $quincenas[$j]->year)
                        ->where('num', ($quincenas[$j]->num)-1)
                        ->select('dt_cut AS cut')
                        ->get(); 
        
                        $inicio = Carbon::parse($inicioAux[0]->cut);
                        $inicio->addDay();
                        $quincenasInicio[$j] = $inicio->format('Y-m-d');
                    }else{
                        $inicioAux = DB::table('hrs_prepay_cut')
                        ->where('year',($quincenas[$j]->year)-1)
                        ->orderBy('num','DESC')
                        ->select('dt_cut AS cut')
                        ->get();
                        
                        $inicio = Carbon::parse($inicioAux[0]->cut);
                        $inicio->addDay();
                        $quincenasInicio[$j] = $inicio->format('Y-m-d');
                    } 
                }
                for ( $i = 0 ; $num_semanas > $i ; $i++ ){
                    for( $j = 0 ; $num_empleados > $j ; $j++ ){
                        $inicio = Carbon::parse($quincenasInicio[$i]);
                        $fin = Carbon::parse($quincenas[$i]->cut);
                        $diferencia = ($inicio->diffInDays($fin))+1;
                        $empl[0] = $empleados[$j]->id;
                        $extratime = $empleados[$j]->extratime;
                        $lRows = SInfoWithPolicy::standardization($quincenasInicio[$i],$quincenas[$i]->cut,$sTypePay,$empl);
                        if(count($lRows) != 0){
                            $lRows = SInfoWithPolicy::handlingHours($lRows,$diferencia,$extratime);
                            //$lRows = SInfoWithPolicy::restDay($lRows,$diferencia);
                            $semanas = SDateUtils::separateBiweekly($quincenas[$i]->id);
                            for ( $x = 0 ; count($semanas) > $x ; $x++ ){
                                $contador = SInfoWithPolicy::restDayBi($lRows,$quincenasInicio[$i],$quincenas[$i]->cut,$semanas[$x],$iYear,$empl[0],$contador);
                            }
                            $contador[0] = 0;
                            $contador[1] = 0;
                            $contador[2] = 0;
                            SInfoWithPolicy::guardarProcesamiento($empleados[$j]->id,$lRows,$quincenas[$i]->id,$iYear,1);
                        }
                    }

                    SInfoWithPolicy::closePeriod( $quincenas[$i]->id, $sTypePay, $inicio->year);    
                }
                
            break;
        }
        }else{

        }
         
      }

      public static function guardarProcesamiento($employee,$lRows,$periodo,$year,$tipo){
        for( $i = 0 ; count($lRows) > $i ; $i++ ){
            $processed = new processed_data();

            $processed->employee_id = $lRows[$i]->idEmployee;
            $processed->inDate = $lRows[$i]->inDateTime;
            $processed->inDateTime = $lRows[$i]->inDateTime;
            $processed->inDateTimeNoficial = $lRows[$i]->inDateTimeNoficial;
            $processed->outDate = $lRows[$i]->outDateTime;
            $processed->outDateTime = $lRows[$i]->outDateTime;
            $processed->outDateTimeNoficial = $lRows[$i]->outDateTimeNoficial;
            $processed->diffMins = $lRows[$i]->diffMins;
            $processed->delayMins = $lRows[$i]->entryDelayMinutes;
            $processed->overDefaultMins = $lRows[$i]->overDefaultMins;
            $processed->overScheduleMins = $lRows[$i]->overScheduleMins;
            $processed->overWorkedMins = $lRows[$i]->overWorkedMins;
            $processed->extraHours = $lRows[$i]->extraHours;
            $processed->is_sunday = $lRows[$i]->isSunday;
            $processed->is_dayoff = $lRows[$i]->isDayOff;
            $processed->is_holiday = $lRows[$i]->isHoliday;
            $processed->others = $lRows[$i]->others;
            $processed->comments = $lRows[$i]->comments;
            $processed->extraDobleMins = $lRows[$i]->extraDoubleMins;
            $processed->extraTripleMins = $lRows[$i]->extraTripleMins;
            $processed->extraDobleMinsNoficial = $lRows[$i]->extraDoubleMinsNoficial;
            $processed->extraTripleMinsNoficial = $lRows[$i]->extraTripleMinsNoficial;
            $processed->indatetimesch = $lRows[$i]->inDateTimeSch;
            $processed->outdatetimesch = $lRows[$i]->outDateTimeSch;
            $processed->prematureout = $lRows[$i]->prematureOut;
            $processed->overminstotal = $lRows[$i]->overMinsTotal;
            $processed->dayinhability = $lRows[$i]->dayInhability;
            $processed->dayvacation = $lRows[$i]->dayVacations;
            $processed->haschecks = $lRows[$i]->hasChecks;
            $processed->hasschedule = $lRows[$i]->hasSchedule;
            $processed->ischecksschedule = $lRows[$i]->isCheckSchedule;
            $processed->istypedaychecked = $lRows[$i]->isTypeDayChecked;
            $processed->hasabsence = $lRows[$i]->hasAbsence;
            $processed->isOverJourney = $lRows[$i]->isOverJourney;
            $processed->hasAdjust = $lRows[$i]->hasAdjust;
            $processed->work_dayoff = $lRows[$i]->work_dayoff;
            if($tipo == 2){
                $processed->week = $periodo;
            }else{
                $processed->biweek = $periodo;
            }
            $processed->year = $year;
            $processed->updated_by = session()->get('user_id');
            $processed->created_by = session()->get('user_id'); 
            $processed->save();
            
        }
      }

      public static function closePeriod ($num_period,$type, $year = 0){
        if( $type == 2 ){

            $period_processed = new period_processed();
            $period_processed->num_week = $num_period;
            $period_processed->year = $year;
            $period_processed->is_week = 1;
            $period_processed->is_close = 0;
            $period_processed->created_by = session()->get('user_id');
            $period_processed->updated_by = session()->get('user_id'); 
            $period_processed->save();

        }else if( $type == 1 ){
            $period_processed = new period_processed();
            $period_processed->num_biweekly = $num_period;
            $period_processed->is_biweekly = 1;
            $period_processed->is_close = 0;
            $period_processed->created_by = session()->get('user_id');
            $period_processed->updated_by = session()->get('user_id');
            $period_processed->save();

        }
      }

      public static function days_works_array($type = 0){
        
        $config = \App\SUtils\SConfiguration::getConfigurations();

        if( $type == 1){
            $dayA = [];
            $dayA[0] = $config->work_days_week->lunes;
            $dayA[1] = $config->work_days_week->martes;
            $dayA[2] = $config->work_days_week->miercoles;
            $dayA[3] = $config->work_days_week->jueves;
            $dayA[4] = $config->work_days_week->viernes;
            $dayA[5] = $config->work_days_week->sabado;
            $dayA[6] = $config->work_days_week->domingo;

        }else{
            $dayA = [];
            $dayA[0] = $config->work_days_week->lunes;
            $dayA[1] = $config->work_days_week->martes;
            $dayA[2] = $config->work_days_week->miercoles;
            $dayA[3] = $config->work_days_week->jueves;
            $dayA[4] = $config->work_days_week->viernes;
            $dayA[5] = $config->work_days_week->sabado;
            $dayA[6] = $config->work_days_week->domingo;
        }

        return $dayA;
      }

      public static function initArr(){
        // cero horas y todos sus minutos
        $arrmin[0][0]=3;
        $arrmin[0][1]=-2;
        $arrmin[0][2]=-3;
        $arrmin[0][3]=-2;
        $arrmin[0][4]=-3;
        $arrmin[0][5]=2;
        $arrmin[0][6]=-1;
        $arrmin[0][7]=1;
        $arrmin[0][8]=9;
        $arrmin[0][9]=7;
        $arrmin[0][10]=-2;
        $arrmin[0][11]=8;
        $arrmin[0][12]=8;
        $arrmin[0][13]=8;
        $arrmin[0][14]=9;
        $arrmin[0][15]=-3;
        $arrmin[0][16]=3;
        $arrmin[0][17]=-2;
        $arrmin[0][18]=3;
        $arrmin[0][19]=6;
        $arrmin[0][20]=-2;
        $arrmin[0][21]=-3;
        $arrmin[0][22]=2;
        $arrmin[0][23]=5;
        $arrmin[0][24]=8;
        $arrmin[0][25]=0;
        $arrmin[0][26]=8;
        $arrmin[0][27]=4;
        $arrmin[0][28]=2;
        $arrmin[0][29]=-1;
        $arrmin[0][30]=5;
        $arrmin[0][31]=-3;
        $arrmin[0][32]=-1;
        $arrmin[0][33]=-1;
        $arrmin[0][34]=-3;
        $arrmin[0][35]=-3;
        $arrmin[0][36]=1;
        $arrmin[0][37]=10;
        $arrmin[0][38]=6;
        $arrmin[0][39]=5;
        $arrmin[0][40]=8;
        $arrmin[0][41]=-1;
        $arrmin[0][42]=9;
        $arrmin[0][43]=6;
        $arrmin[0][44]=4;
        $arrmin[0][45]=10;
        $arrmin[0][46]=3;
        $arrmin[0][47]=6;
        $arrmin[0][48]=4;
        $arrmin[0][49]=2;
        $arrmin[0][50]=9;
        $arrmin[0][51]=1;
        $arrmin[0][52]=7;
        $arrmin[0][53]=0;
        $arrmin[0][54]=2;
        $arrmin[0][55]=-2;
        $arrmin[0][56]=-1;
        $arrmin[0][57]=-2;
        $arrmin[0][58]=-3;
        $arrmin[0][59]=1;
        // una de la mañana y todos sus minutos
        $arrmin[1][0]=-3;
        $arrmin[1][1]=3;
        $arrmin[1][2]=8;
        $arrmin[1][3]=4;
        $arrmin[1][4]=1;
        $arrmin[1][5]=2;
        $arrmin[1][6]=7;
        $arrmin[1][7]=7;
        $arrmin[1][8]=5;
        $arrmin[1][9]=5;
        $arrmin[1][10]=-1;
        $arrmin[1][11]=8;
        $arrmin[1][12]=9;
        $arrmin[1][13]=1;
        $arrmin[1][14]=9;
        $arrmin[1][15]=10;
        $arrmin[1][16]=0;
        $arrmin[1][17]=4;
        $arrmin[1][18]=5;
        $arrmin[1][19]=-3;
        $arrmin[1][20]=6;
        $arrmin[1][21]=-3;
        $arrmin[1][22]=7;
        $arrmin[1][23]=8;
        $arrmin[1][24]=5;
        $arrmin[1][25]=4;
        $arrmin[1][26]=2;
        $arrmin[1][27]=-1;
        $arrmin[1][28]=9;
        $arrmin[1][29]=5;
        $arrmin[1][30]=8;
        $arrmin[1][31]=7;
        $arrmin[1][32]=4;
        $arrmin[1][33]=-2;
        $arrmin[1][34]=6;
        $arrmin[1][35]=8;
        $arrmin[1][36]=-2;
        $arrmin[1][37]=-2;
        $arrmin[1][38]=3;
        $arrmin[1][39]=9;
        $arrmin[1][40]=9;
        $arrmin[1][41]=7;
        $arrmin[1][42]=-3;
        $arrmin[1][43]=2;
        $arrmin[1][44]=5;
        $arrmin[1][45]=10;
        $arrmin[1][46]=2;
        $arrmin[1][47]=3;
        $arrmin[1][48]=9;
        $arrmin[1][49]=7;
        $arrmin[1][50]=0;
        $arrmin[1][51]=3;
        $arrmin[1][52]=10;
        $arrmin[1][53]=5;
        $arrmin[1][54]=6;
        $arrmin[1][55]=3;
        $arrmin[1][56]=6;
        $arrmin[1][57]=10;
        $arrmin[1][58]=6;
        $arrmin[1][59]=-3;
        // 2 de la mañana y todos sus minutos
        $arrmin[2][0]=-3;
        $arrmin[2][1]=7;
        $arrmin[2][2]=-3;
        $arrmin[2][3]=-1;
        $arrmin[2][4]=6;
        $arrmin[2][5]=3;
        $arrmin[2][6]=6;
        $arrmin[2][7]=2;
        $arrmin[2][8]=10;
        $arrmin[2][9]=6;
        $arrmin[2][10]=6;
        $arrmin[2][11]=6;
        $arrmin[2][12]=5;
        $arrmin[2][13]=3;
        $arrmin[2][14]=2;
        $arrmin[2][15]=10;
        $arrmin[2][16]=0;
        $arrmin[2][17]=10;
        $arrmin[2][18]=8;
        $arrmin[2][19]=0;
        $arrmin[2][20]=-3;
        $arrmin[2][21]=5;
        $arrmin[2][22]=5;
        $arrmin[2][23]=9;
        $arrmin[2][24]=5;
        $arrmin[2][25]=-1;
        $arrmin[2][26]=-2;
        $arrmin[2][27]=1;
        $arrmin[2][28]=7;
        $arrmin[2][29]=7;
        $arrmin[2][30]=1;
        $arrmin[2][31]=2;
        $arrmin[2][32]=5;
        $arrmin[2][33]=-3;
        $arrmin[2][34]=4;
        $arrmin[2][35]=-3;
        $arrmin[2][36]=4;
        $arrmin[2][37]=6;
        $arrmin[2][38]=6;
        $arrmin[2][39]=-3;
        $arrmin[2][40]=4;
        $arrmin[2][41]=0;
        $arrmin[2][42]=1;
        $arrmin[2][43]=6;
        $arrmin[2][44]=0;
        $arrmin[2][45]=7;
        $arrmin[2][46]=0;
        $arrmin[2][47]=-1;
        $arrmin[2][48]=0;
        $arrmin[2][49]=-3;
        $arrmin[2][50]=-1;
        $arrmin[2][51]=8;
        $arrmin[2][52]=5;
        $arrmin[2][53]=10;
        $arrmin[2][54]=6;
        $arrmin[2][55]=-3;
        $arrmin[2][56]=5;
        $arrmin[2][57]=8;
        $arrmin[2][58]=10;
        $arrmin[2][59]=-3;
        //tres de la mañana 
        $arrmin[3][0]=10;
        $arrmin[3][1]=4;
        $arrmin[3][2]=10;
        $arrmin[3][3]=1;
        $arrmin[3][4]=7;
        $arrmin[3][5]=4;
        $arrmin[3][6]=-2;
        $arrmin[3][7]=6;
        $arrmin[3][8]=9;
        $arrmin[3][9]=7;
        $arrmin[3][10]=10;
        $arrmin[3][11]=4;
        $arrmin[3][12]=8;
        $arrmin[3][13]=10;
        $arrmin[3][14]=9;
        $arrmin[3][15]=10;
        $arrmin[3][16]=0;
        $arrmin[3][17]=-1;
        $arrmin[3][18]=0;
        $arrmin[3][19]=7;
        $arrmin[3][20]=5;
        $arrmin[3][21]=1;
        $arrmin[3][22]=6;
        $arrmin[3][23]=10;
        $arrmin[3][24]=0;
        $arrmin[3][25]=2;
        $arrmin[3][26]=9;
        $arrmin[3][27]=2;
        $arrmin[3][28]=7;
        $arrmin[3][29]=0;
        $arrmin[3][30]=9;
        $arrmin[3][31]=8;
        $arrmin[3][32]=5;
        $arrmin[3][33]=-3;
        $arrmin[3][34]=2;
        $arrmin[3][35]=10;
        $arrmin[3][36]=9;
        $arrmin[3][37]=-3;
        $arrmin[3][38]=0;
        $arrmin[3][39]=5;
        $arrmin[3][40]=-3;
        $arrmin[3][41]=10;
        $arrmin[3][42]=3;
        $arrmin[3][43]=3;
        $arrmin[3][44]=4;
        $arrmin[3][45]=0;
        $arrmin[3][46]=10;
        $arrmin[3][47]=7;
        $arrmin[3][48]=0;
        $arrmin[3][49]=0;
        $arrmin[3][50]=-1;
        $arrmin[3][51]=0;
        $arrmin[3][52]=9;
        $arrmin[3][53]=9;
        $arrmin[3][54]=-3;
        $arrmin[3][55]=8;
        $arrmin[3][56]=10;
        $arrmin[3][57]=-1;
        $arrmin[3][58]=8;
        $arrmin[3][59]=1;
        // 4 de la mañana
        $arrmin[4][0]=5;
        $arrmin[4][1]=2;
        $arrmin[4][2]=8;
        $arrmin[4][3]=8;
        $arrmin[4][4]=5;
        $arrmin[4][5]=2;
        $arrmin[4][6]=7;
        $arrmin[4][7]=3;
        $arrmin[4][8]=9;
        $arrmin[4][9]=3;
        $arrmin[4][10]=6;
        $arrmin[4][11]=-1;
        $arrmin[4][12]=-1;
        $arrmin[4][13]=2;
        $arrmin[4][14]=10;
        $arrmin[4][15]=1;
        $arrmin[4][16]=7;
        $arrmin[4][17]=2;
        $arrmin[4][18]=6;
        $arrmin[4][19]=7;
        $arrmin[4][20]=3;
        $arrmin[4][21]=10;
        $arrmin[4][22]=-2;
        $arrmin[4][23]=5;
        $arrmin[4][24]=3;
        $arrmin[4][25]=2;
        $arrmin[4][26]=-2;
        $arrmin[4][27]=5;
        $arrmin[4][28]=8;
        $arrmin[4][29]=2;
        $arrmin[4][30]=10;
        $arrmin[4][31]=8;
        $arrmin[4][32]=-2;
        $arrmin[4][33]=5;
        $arrmin[4][34]=5;
        $arrmin[4][35]=3;
        $arrmin[4][36]=10;
        $arrmin[4][37]=-3;
        $arrmin[4][38]=5;
        $arrmin[4][39]=-3;
        $arrmin[4][40]=1;
        $arrmin[4][41]=-2;
        $arrmin[4][42]=10;
        $arrmin[4][43]=5;
        $arrmin[4][44]=8;
        $arrmin[4][45]=8;
        $arrmin[4][46]=6;
        $arrmin[4][47]=0;
        $arrmin[4][48]=0;
        $arrmin[4][49]=-3;
        $arrmin[4][50]=8;
        $arrmin[4][51]=5;
        $arrmin[4][52]=1;
        $arrmin[4][53]=2;
        $arrmin[4][54]=8;
        $arrmin[4][55]=0;
        $arrmin[4][56]=1;
        $arrmin[4][57]=6;
        $arrmin[4][58]=5;
        $arrmin[4][59]=6;

        //5 de la mañana

        $arrmin[5][0]=2;
        $arrmin[5][1]=3;
        $arrmin[5][2]=9;
        $arrmin[5][3]=8;
        $arrmin[5][4]=3;
        $arrmin[5][5]=8;
        $arrmin[5][6]=6;
        $arrmin[5][7]=8;
        $arrmin[5][8]=2;
        $arrmin[5][9]=2;
        $arrmin[5][10]=1;
        $arrmin[5][11]=9;
        $arrmin[5][12]=-2;
        $arrmin[5][13]=-3;
        $arrmin[5][14]=8;
        $arrmin[5][15]=3;
        $arrmin[5][16]=1;
        $arrmin[5][17]=6;
        $arrmin[5][18]=7;
        $arrmin[5][19]=2;
        $arrmin[5][20]=4;
        $arrmin[5][21]=8;
        $arrmin[5][22]=3;
        $arrmin[5][23]=0;
        $arrmin[5][24]=6;
        $arrmin[5][25]=3;
        $arrmin[5][26]=7;
        $arrmin[5][27]=6;
        $arrmin[5][28]=10;
        $arrmin[5][29]=-3;
        $arrmin[5][30]=1;
        $arrmin[5][31]=7;
        $arrmin[5][32]=6;
        $arrmin[5][33]=4;
        $arrmin[5][34]=1;
        $arrmin[5][35]=2;
        $arrmin[5][36]=3;
        $arrmin[5][37]=-2;
        $arrmin[5][38]=8;
        $arrmin[5][39]=5;
        $arrmin[5][40]=8;
        $arrmin[5][41]=9;
        $arrmin[5][42]=6;
        $arrmin[5][43]=2;
        $arrmin[5][44]=0;
        $arrmin[5][45]=0;
        $arrmin[5][46]=10;
        $arrmin[5][47]=2;
        $arrmin[5][48]=1;
        $arrmin[5][49]=4;
        $arrmin[5][50]=-1;
        $arrmin[5][51]=6;
        $arrmin[5][52]=5;
        $arrmin[5][53]=8;
        $arrmin[5][54]=5;
        $arrmin[5][55]=5;
        $arrmin[5][56]=6;
        $arrmin[5][57]=9;
        $arrmin[5][58]=1;
        $arrmin[5][59]=-3;

        // 6 de la mañana

        $arrmin[6][0]=-1;
        $arrmin[6][1]=1;
        $arrmin[6][2]=7;
        $arrmin[6][3]=8;
        $arrmin[6][4]=-1;
        $arrmin[6][5]=5;
        $arrmin[6][6]=3;
        $arrmin[6][7]=7;
        $arrmin[6][8]=4;
        $arrmin[6][9]=5;
        $arrmin[6][10]=9;
        $arrmin[6][11]=3;
        $arrmin[6][12]=6;
        $arrmin[6][13]=-1;
        $arrmin[6][14]=5;
        $arrmin[6][15]=0;
        $arrmin[6][16]=5;
        $arrmin[6][17]=3;
        $arrmin[6][18]=-2;
        $arrmin[6][19]=10;
        $arrmin[6][20]=8;
        $arrmin[6][21]=-3;
        $arrmin[6][22]=-3;
        $arrmin[6][23]=0;
        $arrmin[6][24]=-2;
        $arrmin[6][25]=8;
        $arrmin[6][26]=2;
        $arrmin[6][27]=7;
        $arrmin[6][28]=3;
        $arrmin[6][29]=10;
        $arrmin[6][30]=7;
        $arrmin[6][31]=0;
        $arrmin[6][32]=7;
        $arrmin[6][33]=1;
        $arrmin[6][34]=5;
        $arrmin[6][35]=6;
        $arrmin[6][36]=-2;
        $arrmin[6][37]=-1;
        $arrmin[6][38]=5;
        $arrmin[6][39]=10;
        $arrmin[6][40]=-3;
        $arrmin[6][41]=5;
        $arrmin[6][42]=7;
        $arrmin[6][43]=3;
        $arrmin[6][44]=2;
        $arrmin[6][45]=9;
        $arrmin[6][46]=3;
        $arrmin[6][47]=0;
        $arrmin[6][48]=9;
        $arrmin[6][49]=8;
        $arrmin[6][50]=-2;
        $arrmin[6][51]=4;
        $arrmin[6][52]=9;
        $arrmin[6][53]=0;
        $arrmin[6][54]=1;
        $arrmin[6][55]=8;
        $arrmin[6][56]=9;
        $arrmin[6][57]=-3;
        $arrmin[6][58]=9;
        $arrmin[6][59]=0;

        // 7 de la mañana

        $arrmin[7][0]=5;
        $arrmin[7][1]=9;
        $arrmin[7][2]=5;
        $arrmin[7][3]=-3;
        $arrmin[7][4]=-1;
        $arrmin[7][5]=9;
        $arrmin[7][6]=3;
        $arrmin[7][7]=1;
        $arrmin[7][8]=6;
        $arrmin[7][9]=1;
        $arrmin[7][10]=-1;
        $arrmin[7][11]=-2;
        $arrmin[7][12]=7;
        $arrmin[7][13]=6;
        $arrmin[7][14]=3;
        $arrmin[7][15]=-1;
        $arrmin[7][16]=6;
        $arrmin[7][17]=7;
        $arrmin[7][18]=4;
        $arrmin[7][19]=1;
        $arrmin[7][20]=8;
        $arrmin[7][21]=10;
        $arrmin[7][22]=6;
        $arrmin[7][23]=-3;
        $arrmin[7][24]=-1;
        $arrmin[7][25]=9;
        $arrmin[7][26]=1;
        $arrmin[7][27]=3;
        $arrmin[7][28]=-1;
        $arrmin[7][29]=7;
        $arrmin[7][30]=10;
        $arrmin[7][31]=8;
        $arrmin[7][32]=9;
        $arrmin[7][33]=7;
        $arrmin[7][34]=-2;
        $arrmin[7][35]=6;
        $arrmin[7][36]=5;
        $arrmin[7][37]=9;
        $arrmin[7][38]=9;
        $arrmin[7][39]=6;
        $arrmin[7][40]=1;
        $arrmin[7][41]=1;
        $arrmin[7][42]=-2;
        $arrmin[7][43]=4;
        $arrmin[7][44]=8;
        $arrmin[7][45]=3;
        $arrmin[7][46]=5;
        $arrmin[7][47]=1;
        $arrmin[7][48]=9;
        $arrmin[7][49]=7;
        $arrmin[7][50]=8;
        $arrmin[7][51]=2;
        $arrmin[7][52]=6;
        $arrmin[7][53]=-3;
        $arrmin[7][54]=8;
        $arrmin[7][55]=7;
        $arrmin[7][56]=4;
        $arrmin[7][57]=-3;
        $arrmin[7][58]=6;
        $arrmin[7][59]=-3;

        //8 de la mañana

        $arrmin[8][0]=1;
        $arrmin[8][1]=4;
        $arrmin[8][2]=7;
        $arrmin[8][3]=0;
        $arrmin[8][4]=8;
        $arrmin[8][5]=3;
        $arrmin[8][6]=1;
        $arrmin[8][7]=0;
        $arrmin[8][8]=-3;
        $arrmin[8][9]=6;
        $arrmin[8][10]=5;
        $arrmin[8][11]=10;
        $arrmin[8][12]=10;
        $arrmin[8][13]=6;
        $arrmin[8][14]=2;
        $arrmin[8][15]=-2;
        $arrmin[8][16]=-1;
        $arrmin[8][17]=4;
        $arrmin[8][18]=0;
        $arrmin[8][19]=6;
        $arrmin[8][20]=4;
        $arrmin[8][21]=6;
        $arrmin[8][22]=-1;
        $arrmin[8][23]=0;
        $arrmin[8][24]=4;
        $arrmin[8][25]=9;
        $arrmin[8][26]=9;
        $arrmin[8][27]=6;
        $arrmin[8][28]=4;
        $arrmin[8][29]=9;
        $arrmin[8][30]=5;
        $arrmin[8][31]=2;
        $arrmin[8][32]=2;
        $arrmin[8][33]=3;
        $arrmin[8][34]=2;
        $arrmin[8][35]=1;
        $arrmin[8][36]=6;
        $arrmin[8][37]=2;
        $arrmin[8][38]=9;
        $arrmin[8][39]=0;
        $arrmin[8][40]=4;
        $arrmin[8][41]=2;
        $arrmin[8][42]=1;
        $arrmin[8][43]=-2;
        $arrmin[8][44]=8;
        $arrmin[8][45]=9;
        $arrmin[8][46]=3;
        $arrmin[8][47]=2;
        $arrmin[8][48]=-3;
        $arrmin[8][49]=7;
        $arrmin[8][50]=-3;
        $arrmin[8][51]=0;
        $arrmin[8][52]=5;
        $arrmin[8][53]=0;
        $arrmin[8][54]=4;
        $arrmin[8][55]=1;
        $arrmin[8][56]=2;
        $arrmin[8][57]=4;
        $arrmin[8][58]=9;
        $arrmin[8][59]=5;

        //9 de la mañana

        $arrmin[9][0]=-3;
        $arrmin[9][1]=7;
        $arrmin[9][2]=-2;
        $arrmin[9][3]=1;
        $arrmin[9][4]=4;
        $arrmin[9][5]=1;
        $arrmin[9][6]=3;
        $arrmin[9][7]=-3;
        $arrmin[9][8]=2;
        $arrmin[9][9]=10;
        $arrmin[9][10]=3;
        $arrmin[9][11]=8;
        $arrmin[9][12]=-3;
        $arrmin[9][13]=10;
        $arrmin[9][14]=0;
        $arrmin[9][15]=6;
        $arrmin[9][16]=4;
        $arrmin[9][17]=2;
        $arrmin[9][18]=3;
        $arrmin[9][19]=2;
        $arrmin[9][20]=2;
        $arrmin[9][21]=2;
        $arrmin[9][22]=4;
        $arrmin[9][23]=2;
        $arrmin[9][24]=0;
        $arrmin[9][25]=9;
        $arrmin[9][26]=-2;
        $arrmin[9][27]=10;
        $arrmin[9][28]=0;
        $arrmin[9][29]=-3;
        $arrmin[9][30]=8;
        $arrmin[9][31]=1;
        $arrmin[9][32]=4;
        $arrmin[9][33]=-2;
        $arrmin[9][34]=-3;
        $arrmin[9][35]=-3;
        $arrmin[9][36]=0;
        $arrmin[9][37]=9;
        $arrmin[9][38]=5;
        $arrmin[9][39]=-2;
        $arrmin[9][40]=10;
        $arrmin[9][41]=-3;
        $arrmin[9][42]=1;
        $arrmin[9][43]=2;
        $arrmin[9][44]=2;
        $arrmin[9][45]=6;
        $arrmin[9][46]=-2;
        $arrmin[9][47]=8;
        $arrmin[9][48]=3;
        $arrmin[9][49]=9;
        $arrmin[9][50]=6;
        $arrmin[9][51]=-2;
        $arrmin[9][52]=-2;
        $arrmin[9][53]=-1;
        $arrmin[9][54]=1;
        $arrmin[9][55]=1;
        $arrmin[9][56]=-1;
        $arrmin[9][57]=0;
        $arrmin[9][58]=7;
        $arrmin[9][59]=0;

        // 10 de la mañana

        $arrmin[10][0]=2;
        $arrmin[10][1]=10;
        $arrmin[10][2]=-3;
        $arrmin[10][3]=7;
        $arrmin[10][4]=-3;
        $arrmin[10][5]=0;
        $arrmin[10][6]=0;
        $arrmin[10][7]=5;
        $arrmin[10][8]=10;
        $arrmin[10][9]=5;
        $arrmin[10][10]=10;
        $arrmin[10][11]=2;
        $arrmin[10][12]=10;
        $arrmin[10][13]=9;
        $arrmin[10][14]=3;
        $arrmin[10][15]=6;
        $arrmin[10][16]=7;
        $arrmin[10][17]=6;
        $arrmin[10][18]=2;
        $arrmin[10][19]=9;
        $arrmin[10][20]=3;
        $arrmin[10][21]=5;
        $arrmin[10][22]=6;
        $arrmin[10][23]=9;
        $arrmin[10][24]=3;
        $arrmin[10][25]=3;
        $arrmin[10][26]=9;
        $arrmin[10][27]=5;
        $arrmin[10][28]=2;
        $arrmin[10][29]=-1;
        $arrmin[10][30]=2;
        $arrmin[10][31]=5;
        $arrmin[10][32]=7;
        $arrmin[10][33]=9;
        $arrmin[10][34]=-2;
        $arrmin[10][35]=3;
        $arrmin[10][36]=0;
        $arrmin[10][37]=-2;
        $arrmin[10][38]=-2;
        $arrmin[10][39]=5;
        $arrmin[10][40]=-3;
        $arrmin[10][41]=5;
        $arrmin[10][42]=6;
        $arrmin[10][43]=-2;
        $arrmin[10][44]=8;
        $arrmin[10][45]=7;
        $arrmin[10][46]=1;
        $arrmin[10][47]=-2;
        $arrmin[10][48]=9;
        $arrmin[10][49]=0;
        $arrmin[10][50]=-2;
        $arrmin[10][51]=4;
        $arrmin[10][52]=10;
        $arrmin[10][53]=9;
        $arrmin[10][54]=-2;
        $arrmin[10][55]=3;
        $arrmin[10][56]=-1;
        $arrmin[10][57]=2;
        $arrmin[10][58]=4;
        $arrmin[10][59]=6;

        //11 de la mañana

        $arrmin[11][0]=-1;
        $arrmin[11][1]=-2;
        $arrmin[11][2]=3;
        $arrmin[11][3]=8;
        $arrmin[11][4]=-3;
        $arrmin[11][5]=9;
        $arrmin[11][6]=1;
        $arrmin[11][7]=6;
        $arrmin[11][8]=9;
        $arrmin[11][9]=0;
        $arrmin[11][10]=3;
        $arrmin[11][11]=1;
        $arrmin[11][12]=9;
        $arrmin[11][13]=3;
        $arrmin[11][14]=8;
        $arrmin[11][15]=9;
        $arrmin[11][16]=5;
        $arrmin[11][17]=4;
        $arrmin[11][18]=9;
        $arrmin[11][19]=3;
        $arrmin[11][20]=3;
        $arrmin[11][21]=8;
        $arrmin[11][22]=9;
        $arrmin[11][23]=7;
        $arrmin[11][24]=10;
        $arrmin[11][25]=10;
        $arrmin[11][26]=5;
        $arrmin[11][27]=2;
        $arrmin[11][28]=8;
        $arrmin[11][29]=7;
        $arrmin[11][30]=0;
        $arrmin[11][31]=3;
        $arrmin[11][32]=2;
        $arrmin[11][33]=10;
        $arrmin[11][34]=6;
        $arrmin[11][35]=6;
        $arrmin[11][36]=8;
        $arrmin[11][37]=10;
        $arrmin[11][38]=3;
        $arrmin[11][39]=8;
        $arrmin[11][40]=0;
        $arrmin[11][41]=8;
        $arrmin[11][42]=-2;
        $arrmin[11][43]=9;
        $arrmin[11][44]=10;
        $arrmin[11][45]=4;
        $arrmin[11][46]=8;
        $arrmin[11][47]=-1;
        $arrmin[11][48]=5;
        $arrmin[11][49]=4;
        $arrmin[11][50]=5;
        $arrmin[11][51]=7;
        $arrmin[11][52]=8;
        $arrmin[11][53]=2;
        $arrmin[11][54]=8;
        $arrmin[11][55]=-1;
        $arrmin[11][56]=-2;
        $arrmin[11][57]=-1;
        $arrmin[11][58]=8;
        $arrmin[11][59]=-1;

        //12 de la tarde

        $arrmin[12][0]=9;
        $arrmin[12][1]=9;
        $arrmin[12][2]=6;
        $arrmin[12][3]=-3;
        $arrmin[12][4]=7;
        $arrmin[12][5]=0;
        $arrmin[12][6]=6;
        $arrmin[12][7]=6;
        $arrmin[12][8]=3;
        $arrmin[12][9]=8;
        $arrmin[12][10]=2;
        $arrmin[12][11]=7;
        $arrmin[12][12]=8;
        $arrmin[12][13]=9;
        $arrmin[12][14]=4;
        $arrmin[12][15]=4;
        $arrmin[12][16]=1;
        $arrmin[12][17]=1;
        $arrmin[12][18]=3;
        $arrmin[12][19]=4;
        $arrmin[12][20]=0;
        $arrmin[12][21]=7;
        $arrmin[12][22]=1;
        $arrmin[12][23]=-3;
        $arrmin[12][24]=-1;
        $arrmin[12][25]=6;
        $arrmin[12][26]=0;
        $arrmin[12][27]=-1;
        $arrmin[12][28]=7;
        $arrmin[12][29]=4;
        $arrmin[12][30]=9;
        $arrmin[12][31]=6;
        $arrmin[12][32]=3;
        $arrmin[12][33]=0;
        $arrmin[12][34]=-2;
        $arrmin[12][35]=1;
        $arrmin[12][36]=-2;
        $arrmin[12][37]=-1;
        $arrmin[12][38]=2;
        $arrmin[12][39]=-1;
        $arrmin[12][40]=3;
        $arrmin[12][41]=-2;
        $arrmin[12][42]=3;
        $arrmin[12][43]=1;
        $arrmin[12][44]=2;
        $arrmin[12][45]=1;
        $arrmin[12][46]=6;
        $arrmin[12][47]=3;
        $arrmin[12][48]=3;
        $arrmin[12][49]=-2;
        $arrmin[12][50]=0;
        $arrmin[12][51]=0;
        $arrmin[12][52]=6;
        $arrmin[12][53]=10;
        $arrmin[12][54]=5;
        $arrmin[12][55]=0;
        $arrmin[12][56]=2;
        $arrmin[12][57]=10;
        $arrmin[12][58]=0;
        $arrmin[12][59]=5;

        // 1 de la tarde

        $arrmin[13][0]=1;
        $arrmin[13][1]=-2;
        $arrmin[13][2]=8;
        $arrmin[13][3]=4;
        $arrmin[13][4]=7;
        $arrmin[13][5]=6;
        $arrmin[13][6]=9;
        $arrmin[13][7]=9;
        $arrmin[13][8]=4;
        $arrmin[13][9]=0;
        $arrmin[13][10]=2;
        $arrmin[13][11]=5;
        $arrmin[13][12]=2;
        $arrmin[13][13]=7;
        $arrmin[13][14]=3;
        $arrmin[13][15]=3;
        $arrmin[13][16]=10;
        $arrmin[13][17]=7;
        $arrmin[13][18]=-1;
        $arrmin[13][19]=4;
        $arrmin[13][20]=0;
        $arrmin[13][21]=10;
        $arrmin[13][22]=10;
        $arrmin[13][23]=2;
        $arrmin[13][24]=2;
        $arrmin[13][25]=9;
        $arrmin[13][26]=10;
        $arrmin[13][27]=5;
        $arrmin[13][28]=3;
        $arrmin[13][29]=6;
        $arrmin[13][30]=2;
        $arrmin[13][31]=7;
        $arrmin[13][32]=-1;
        $arrmin[13][33]=9;
        $arrmin[13][34]=0;
        $arrmin[13][35]=-1;
        $arrmin[13][36]=-2;
        $arrmin[13][37]=10;
        $arrmin[13][38]=4;
        $arrmin[13][39]=4;
        $arrmin[13][40]=8;
        $arrmin[13][41]=3;
        $arrmin[13][42]=-2;
        $arrmin[13][43]=10;
        $arrmin[13][44]=9;
        $arrmin[13][45]=7;
        $arrmin[13][46]=8;
        $arrmin[13][47]=-1;
        $arrmin[13][48]=5;
        $arrmin[13][49]=-2;
        $arrmin[13][50]=9;
        $arrmin[13][51]=7;
        $arrmin[13][52]=7;
        $arrmin[13][53]=4;
        $arrmin[13][54]=9;
        $arrmin[13][55]=3;
        $arrmin[13][56]=2;
        $arrmin[13][57]=-3;
        $arrmin[13][58]=-1;
        $arrmin[13][59]=-1;

        //2 de la tarde

        $arrmin[14][0]=4;
        $arrmin[14][1]=3;
        $arrmin[14][2]=4;
        $arrmin[14][3]=0;
        $arrmin[14][4]=10;
        $arrmin[14][5]=6;
        $arrmin[14][6]=-3;
        $arrmin[14][7]=8;
        $arrmin[14][8]=2;
        $arrmin[14][9]=8;
        $arrmin[14][10]=-3;
        $arrmin[14][11]=10;
        $arrmin[14][12]=0;
        $arrmin[14][13]=7;
        $arrmin[14][14]=-2;
        $arrmin[14][15]=5;
        $arrmin[14][16]=10;
        $arrmin[14][17]=3;
        $arrmin[14][18]=7;
        $arrmin[14][19]=-3;
        $arrmin[14][20]=6;
        $arrmin[14][21]=1;
        $arrmin[14][22]=4;
        $arrmin[14][23]=6;
        $arrmin[14][24]=2;
        $arrmin[14][25]=7;
        $arrmin[14][26]=9;
        $arrmin[14][27]=-1;
        $arrmin[14][28]=2;
        $arrmin[14][29]=2;
        $arrmin[14][30]=3;
        $arrmin[14][31]=9;
        $arrmin[14][32]=5;
        $arrmin[14][33]=4;
        $arrmin[14][34]=10;
        $arrmin[14][35]=7;
        $arrmin[14][36]=9;
        $arrmin[14][37]=3;
        $arrmin[14][38]=5;
        $arrmin[14][39]=5;
        $arrmin[14][40]=-1;
        $arrmin[14][41]=0;
        $arrmin[14][42]=4;
        $arrmin[14][43]=4;
        $arrmin[14][44]=-1;
        $arrmin[14][45]=6;
        $arrmin[14][46]=9;
        $arrmin[14][47]=1;
        $arrmin[14][48]=2;
        $arrmin[14][49]=10;
        $arrmin[14][50]=8;
        $arrmin[14][51]=9;
        $arrmin[14][52]=6;
        $arrmin[14][53]=2;
        $arrmin[14][54]=10;
        $arrmin[14][55]=-2;
        $arrmin[14][56]=3;
        $arrmin[14][57]=7;
        $arrmin[14][58]=7;
        $arrmin[14][59]=3;

        // 3 de la tarde

        $arrmin[15][0]=-3;
        $arrmin[15][1]=10;
        $arrmin[15][2]=7;
        $arrmin[15][3]=-1;
        $arrmin[15][4]=-2;
        $arrmin[15][5]=-1;
        $arrmin[15][6]=5;
        $arrmin[15][7]=5;
        $arrmin[15][8]=10;
        $arrmin[15][9]=1;
        $arrmin[15][10]=4;
        $arrmin[15][11]=3;
        $arrmin[15][12]=-3;
        $arrmin[15][13]=9;
        $arrmin[15][14]=3;
        $arrmin[15][15]=-2;
        $arrmin[15][16]=2;
        $arrmin[15][17]=9;
        $arrmin[15][18]=2;
        $arrmin[15][19]=6;
        $arrmin[15][20]=10;
        $arrmin[15][21]=4;
        $arrmin[15][22]=4;
        $arrmin[15][23]=2;
        $arrmin[15][24]=1;
        $arrmin[15][25]=-1;
        $arrmin[15][26]=10;
        $arrmin[15][27]=2;
        $arrmin[15][28]=7;
        $arrmin[15][29]=1;
        $arrmin[15][30]=6;
        $arrmin[15][31]=9;
        $arrmin[15][32]=-1;
        $arrmin[15][33]=0;
        $arrmin[15][34]=0;
        $arrmin[15][35]=-3;
        $arrmin[15][36]=5;
        $arrmin[15][37]=-3;
        $arrmin[15][38]=-1;
        $arrmin[15][39]=-1;
        $arrmin[15][40]=-3;
        $arrmin[15][41]=9;
        $arrmin[15][42]=9;
        $arrmin[15][43]=7;
        $arrmin[15][44]=8;
        $arrmin[15][45]=8;
        $arrmin[15][46]=4;
        $arrmin[15][47]=3;
        $arrmin[15][48]=0;
        $arrmin[15][49]=5;
        $arrmin[15][50]=0;
        $arrmin[15][51]=7;
        $arrmin[15][52]=0;
        $arrmin[15][53]=-3;
        $arrmin[15][54]=1;
        $arrmin[15][55]=1;
        $arrmin[15][56]=9;
        $arrmin[15][57]=-2;
        $arrmin[15][58]=-1;
        $arrmin[15][59]=2;

        // 4 de la tarde
        $arrmin[16][0]=-1;
        $arrmin[16][1]=5;
        $arrmin[16][2]=7;
        $arrmin[16][3]=8;
        $arrmin[16][4]=7;
        $arrmin[16][5]=1;
        $arrmin[16][6]=-2;
        $arrmin[16][7]=8;
        $arrmin[16][8]=-2;
        $arrmin[16][9]=8;
        $arrmin[16][10]=-2;
        $arrmin[16][11]=4;
        $arrmin[16][12]=1;
        $arrmin[16][13]=7;
        $arrmin[16][14]=-2;
        $arrmin[16][15]=2;
        $arrmin[16][16]=1;
        $arrmin[16][17]=8;
        $arrmin[16][18]=7;
        $arrmin[16][19]=9;
        $arrmin[16][20]=10;
        $arrmin[16][21]=-3;
        $arrmin[16][22]=-2;
        $arrmin[16][23]=7;
        $arrmin[16][24]=9;
        $arrmin[16][25]=6;
        $arrmin[16][26]=2;
        $arrmin[16][27]=7;
        $arrmin[16][28]=1;
        $arrmin[16][29]=0;
        $arrmin[16][30]=0;
        $arrmin[16][31]=-1;
        $arrmin[16][32]=2;
        $arrmin[16][33]=-3;
        $arrmin[16][34]=9;
        $arrmin[16][35]=1;
        $arrmin[16][36]=0;
        $arrmin[16][37]=-3;
        $arrmin[16][38]=7;
        $arrmin[16][39]=6;
        $arrmin[16][40]=10;
        $arrmin[16][41]=5;
        $arrmin[16][42]=7;
        $arrmin[16][43]=6;
        $arrmin[16][44]=1;
        $arrmin[16][45]=-2;
        $arrmin[16][46]=5;
        $arrmin[16][47]=4;
        $arrmin[16][48]=-3;
        $arrmin[16][49]=5;
        $arrmin[16][50]=0;
        $arrmin[16][51]=-2;
        $arrmin[16][52]=5;
        $arrmin[16][53]=2;
        $arrmin[16][54]=-1;
        $arrmin[16][55]=2;
        $arrmin[16][56]=5;
        $arrmin[16][57]=1;
        $arrmin[16][58]=0;
        $arrmin[16][59]=3;

        // 5 de la tarde

        $arrmin[17][0]=4;
        $arrmin[17][1]=5;
        $arrmin[17][2]=8;
        $arrmin[17][3]=2;
        $arrmin[17][4]=-3;
        $arrmin[17][5]=0;
        $arrmin[17][6]=2;
        $arrmin[17][7]=0;
        $arrmin[17][8]=9;
        $arrmin[17][9]=3;
        $arrmin[17][10]=2;
        $arrmin[17][11]=6;
        $arrmin[17][12]=-3;
        $arrmin[17][13]=8;
        $arrmin[17][14]=6;
        $arrmin[17][15]=-2;
        $arrmin[17][16]=7;
        $arrmin[17][17]=-2;
        $arrmin[17][18]=1;
        $arrmin[17][19]=-1;
        $arrmin[17][20]=-1;
        $arrmin[17][21]=10;
        $arrmin[17][22]=5;
        $arrmin[17][23]=10;
        $arrmin[17][24]=-3;
        $arrmin[17][25]=2;
        $arrmin[17][26]=2;
        $arrmin[17][27]=6;
        $arrmin[17][28]=4;
        $arrmin[17][29]=2;
        $arrmin[17][30]=0;
        $arrmin[17][31]=-2;
        $arrmin[17][32]=1;
        $arrmin[17][33]=0;
        $arrmin[17][34]=5;
        $arrmin[17][35]=5;
        $arrmin[17][36]=8;
        $arrmin[17][37]=10;
        $arrmin[17][38]=10;
        $arrmin[17][39]=8;
        $arrmin[17][40]=9;
        $arrmin[17][41]=9;
        $arrmin[17][42]=5;
        $arrmin[17][43]=3;
        $arrmin[17][44]=9;
        $arrmin[17][45]=-3;
        $arrmin[17][46]=0;
        $arrmin[17][47]=0;
        $arrmin[17][48]=7;
        $arrmin[17][49]=4;
        $arrmin[17][50]=3;
        $arrmin[17][51]=9;
        $arrmin[17][52]=10;
        $arrmin[17][53]=9;
        $arrmin[17][54]=4;
        $arrmin[17][55]=-3;
        $arrmin[17][56]=-1;
        $arrmin[17][57]=6;
        $arrmin[17][58]=-1;
        $arrmin[17][59]=6;

        // 6 de la tarde
        
        $arrmin[18][0]=5;
        $arrmin[18][1]=7;
        $arrmin[18][2]=7;
        $arrmin[18][3]=7;
        $arrmin[18][4]=10;
        $arrmin[18][5]=9;
        $arrmin[18][6]=1;
        $arrmin[18][7]=-2;
        $arrmin[18][8]=1;
        $arrmin[18][9]=-3;
        $arrmin[18][10]=-3;
        $arrmin[18][11]=6;
        $arrmin[18][12]=10;
        $arrmin[18][13]=7;
        $arrmin[18][14]=1;
        $arrmin[18][15]=2;
        $arrmin[18][16]=0;
        $arrmin[18][17]=6;
        $arrmin[18][18]=7;
        $arrmin[18][19]=6;
        $arrmin[18][20]=7;
        $arrmin[18][21]=-2;
        $arrmin[18][22]=5;
        $arrmin[18][23]=3;
        $arrmin[18][24]=-1;
        $arrmin[18][25]=10;
        $arrmin[18][26]=4;
        $arrmin[18][27]=4;
        $arrmin[18][28]=-2;
        $arrmin[18][29]=-3;
        $arrmin[18][30]=10;
        $arrmin[18][31]=1;
        $arrmin[18][32]=2;
        $arrmin[18][33]=3;
        $arrmin[18][34]=9;
        $arrmin[18][35]=0;
        $arrmin[18][36]=-2;
        $arrmin[18][37]=9;
        $arrmin[18][38]=-3;
        $arrmin[18][39]=4;
        $arrmin[18][40]=-2;
        $arrmin[18][41]=-3;
        $arrmin[18][42]=10;
        $arrmin[18][43]=2;
        $arrmin[18][44]=0;
        $arrmin[18][45]=3;
        $arrmin[18][46]=8;
        $arrmin[18][47]=2;
        $arrmin[18][48]=9;
        $arrmin[18][49]=3;
        $arrmin[18][50]=8;
        $arrmin[18][51]=-3;
        $arrmin[18][52]=-3;
        $arrmin[18][53]=5;
        $arrmin[18][54]=6;
        $arrmin[18][55]=10;
        $arrmin[18][56]=7;
        $arrmin[18][57]=4;
        $arrmin[18][58]=4;
        $arrmin[18][59]=1;

        // 7 de la tarde

        $arrmin[19][0]=8;
        $arrmin[19][1]=9;
        $arrmin[19][2]=6;
        $arrmin[19][3]=6;
        $arrmin[19][4]=-3;
        $arrmin[19][5]=-2;
        $arrmin[19][6]=8;
        $arrmin[19][7]=8;
        $arrmin[19][8]=-3;
        $arrmin[19][9]=9;
        $arrmin[19][10]=8;
        $arrmin[19][11]=9;
        $arrmin[19][12]=-1;
        $arrmin[19][13]=7;
        $arrmin[19][14]=-2;
        $arrmin[19][15]=0;
        $arrmin[19][16]=3;
        $arrmin[19][17]=0;
        $arrmin[19][18]=-2;
        $arrmin[19][19]=3;
        $arrmin[19][20]=-2;
        $arrmin[19][21]=8;
        $arrmin[19][22]=-3;
        $arrmin[19][23]=2;
        $arrmin[19][24]=2;
        $arrmin[19][25]=6;
        $arrmin[19][26]=7;
        $arrmin[19][27]=9;
        $arrmin[19][28]=4;
        $arrmin[19][29]=-3;
        $arrmin[19][30]=2;
        $arrmin[19][31]=9;
        $arrmin[19][32]=7;
        $arrmin[19][33]=-2;
        $arrmin[19][34]=-1;
        $arrmin[19][35]=1;
        $arrmin[19][36]=7;
        $arrmin[19][37]=-2;
        $arrmin[19][38]=-2;
        $arrmin[19][39]=6;
        $arrmin[19][40]=4;
        $arrmin[19][41]=6;
        $arrmin[19][42]=9;
        $arrmin[19][43]=8;
        $arrmin[19][44]=3;
        $arrmin[19][45]=-1;
        $arrmin[19][46]=5;
        $arrmin[19][47]=1;
        $arrmin[19][48]=4;
        $arrmin[19][49]=4;
        $arrmin[19][50]=-2;
        $arrmin[19][51]=1;
        $arrmin[19][52]=10;
        $arrmin[19][53]=8;
        $arrmin[19][54]=3;
        $arrmin[19][55]=1;
        $arrmin[19][56]=2;
        $arrmin[19][57]=9;
        $arrmin[19][58]=-1;
        $arrmin[19][59]=0;

        // 8 de la tarde

        $arrmin[20][0]=3;
        $arrmin[20][1]=-1;
        $arrmin[20][2]=-2;
        $arrmin[20][3]=3;
        $arrmin[20][4]=1;
        $arrmin[20][5]=0;
        $arrmin[20][6]=6;
        $arrmin[20][7]=-2;
        $arrmin[20][8]=9;
        $arrmin[20][9]=-1;
        $arrmin[20][10]=-2;
        $arrmin[20][11]=2;
        $arrmin[20][12]=2;
        $arrmin[20][13]=-2;
        $arrmin[20][14]=9;
        $arrmin[20][15]=-1;
        $arrmin[20][16]=3;
        $arrmin[20][17]=6;
        $arrmin[20][18]=2;
        $arrmin[20][19]=4;
        $arrmin[20][20]=6;
        $arrmin[20][21]=3;
        $arrmin[20][22]=4;
        $arrmin[20][23]=-3;
        $arrmin[20][24]=8;
        $arrmin[20][25]=4;
        $arrmin[20][26]=3;
        $arrmin[20][27]=7;
        $arrmin[20][28]=2;
        $arrmin[20][29]=6;
        $arrmin[20][30]=3;
        $arrmin[20][31]=10;
        $arrmin[20][32]=9;
        $arrmin[20][33]=9;
        $arrmin[20][34]=6;
        $arrmin[20][35]=3;
        $arrmin[20][36]=7;
        $arrmin[20][37]=8;
        $arrmin[20][38]=0;
        $arrmin[20][39]=-1;
        $arrmin[20][40]=1;
        $arrmin[20][41]=5;
        $arrmin[20][42]=1;
        $arrmin[20][43]=6;
        $arrmin[20][44]=8;
        $arrmin[20][45]=-3;
        $arrmin[20][46]=10;
        $arrmin[20][47]=-2;
        $arrmin[20][48]=2;
        $arrmin[20][49]=6;
        $arrmin[20][50]=0;
        $arrmin[20][51]=1;
        $arrmin[20][52]=-1;
        $arrmin[20][53]=3;
        $arrmin[20][54]=-2;
        $arrmin[20][55]=8;
        $arrmin[20][56]=0;
        $arrmin[20][57]=4;
        $arrmin[20][58]=0;
        $arrmin[20][59]=-2;

        // 9 de la tarde
      
        $arrmin[21][0]=6;
        $arrmin[21][1]=10;
        $arrmin[21][2]=4;
        $arrmin[21][3]=-1;
        $arrmin[21][4]=3;
        $arrmin[21][5]=0;
        $arrmin[21][6]=-3;
        $arrmin[21][7]=-3;
        $arrmin[21][8]=0;
        $arrmin[21][9]=10;
        $arrmin[21][10]=10;
        $arrmin[21][11]=4;
        $arrmin[21][12]=2;
        $arrmin[21][13]=-2;
        $arrmin[21][14]=5;
        $arrmin[21][15]=-1;
        $arrmin[21][16]=-3;
        $arrmin[21][17]=5;
        $arrmin[21][18]=2;
        $arrmin[21][19]=1;
        $arrmin[21][20]=10;
        $arrmin[21][21]=6;
        $arrmin[21][22]=5;
        $arrmin[21][23]=-3;
        $arrmin[21][24]=2;
        $arrmin[21][25]=2;
        $arrmin[21][26]=-1;
        $arrmin[21][27]=5;
        $arrmin[21][28]=4;
        $arrmin[21][29]=9;
        $arrmin[21][30]=10;
        $arrmin[21][31]=-3;
        $arrmin[21][32]=10;
        $arrmin[21][33]=10;
        $arrmin[21][34]=8;
        $arrmin[21][35]=9;
        $arrmin[21][36]=9;
        $arrmin[21][37]=9;
        $arrmin[21][38]=9;
        $arrmin[21][39]=5;
        $arrmin[21][40]=3;
        $arrmin[21][41]=9;
        $arrmin[21][42]=9;
        $arrmin[21][43]=4;
        $arrmin[21][44]=10;
        $arrmin[21][45]=5;
        $arrmin[21][46]=3;
        $arrmin[21][47]=-1;
        $arrmin[21][48]=9;
        $arrmin[21][49]=3;
        $arrmin[21][50]=1;
        $arrmin[21][51]=1;
        $arrmin[21][52]=2;
        $arrmin[21][53]=8;
        $arrmin[21][54]=2;
        $arrmin[21][55]=6;
        $arrmin[21][56]=1;
        $arrmin[21][57]=10;
        $arrmin[21][58]=10;
        $arrmin[21][59]=-2;

        // 10 de la tarde

        $arrmin[22][0]=7;
        $arrmin[22][1]=10;
        $arrmin[22][2]=8;
        $arrmin[22][3]=8;
        $arrmin[22][4]=10;
        $arrmin[22][5]=9;
        $arrmin[22][6]=4;
        $arrmin[22][7]=-2;
        $arrmin[22][8]=1;
        $arrmin[22][9]=9;
        $arrmin[22][10]=-1;
        $arrmin[22][11]=0;
        $arrmin[22][12]=2;
        $arrmin[22][13]=1;
        $arrmin[22][14]=9;
        $arrmin[22][15]=-2;
        $arrmin[22][16]=-3;
        $arrmin[22][17]=6;
        $arrmin[22][18]=10;
        $arrmin[22][19]=-2;
        $arrmin[22][20]=8;
        $arrmin[22][21]=-2;
        $arrmin[22][22]=9;
        $arrmin[22][23]=3;
        $arrmin[22][24]=7;
        $arrmin[22][25]=7;
        $arrmin[22][26]=2;
        $arrmin[22][27]=6;
        $arrmin[22][28]=9;
        $arrmin[22][29]=6;
        $arrmin[22][30]=10;
        $arrmin[22][31]=6;
        $arrmin[22][32]=2;
        $arrmin[22][33]=7;
        $arrmin[22][34]=4;
        $arrmin[22][35]=-2;
        $arrmin[22][36]=-2;
        $arrmin[22][37]=-2;
        $arrmin[22][38]=2;
        $arrmin[22][39]=9;
        $arrmin[22][40]=4;
        $arrmin[22][41]=1;
        $arrmin[22][42]=3;
        $arrmin[22][43]=9;
        $arrmin[22][44]=10;
        $arrmin[22][45]=-2;
        $arrmin[22][46]=10;
        $arrmin[22][47]=-2;
        $arrmin[22][48]=-1;
        $arrmin[22][49]=9;
        $arrmin[22][50]=-1;
        $arrmin[22][51]=5;
        $arrmin[22][52]=7;
        $arrmin[22][53]=3;
        $arrmin[22][54]=-3;
        $arrmin[22][55]=0;
        $arrmin[22][56]=-3;
        $arrmin[22][57]=5;
        $arrmin[22][58]=-1;
        $arrmin[22][59]=6;

        // 11 de la tarde

        $arrmin[23][0]=5;
        $arrmin[23][1]=-2;
        $arrmin[23][2]=7;
        $arrmin[23][3]=-1;
        $arrmin[23][4]=3;
        $arrmin[23][5]=10;
        $arrmin[23][6]=2;
        $arrmin[23][7]=7;
        $arrmin[23][8]=2;
        $arrmin[23][9]=10;
        $arrmin[23][10]=7;
        $arrmin[23][11]=8;
        $arrmin[23][12]=1;
        $arrmin[23][13]=-1;
        $arrmin[23][14]=10;
        $arrmin[23][15]=10;
        $arrmin[23][16]=2;
        $arrmin[23][17]=6;
        $arrmin[23][18]=-2;
        $arrmin[23][19]=9;
        $arrmin[23][20]=6;
        $arrmin[23][21]=5;
        $arrmin[23][22]=3;
        $arrmin[23][23]=10;
        $arrmin[23][24]=3;
        $arrmin[23][25]=-3;
        $arrmin[23][26]=7;
        $arrmin[23][27]=-2;
        $arrmin[23][28]=4;
        $arrmin[23][29]=-1;
        $arrmin[23][30]=6;
        $arrmin[23][31]=6;
        $arrmin[23][32]=-3;
        $arrmin[23][33]=-3;
        $arrmin[23][34]=3;
        $arrmin[23][35]=0;
        $arrmin[23][36]=3;
        $arrmin[23][37]=-3;
        $arrmin[23][38]=8;
        $arrmin[23][39]=3;
        $arrmin[23][40]=0;
        $arrmin[23][41]=5;
        $arrmin[23][42]=0;
        $arrmin[23][43]=3;
        $arrmin[23][44]=9;
        $arrmin[23][45]=-3;
        $arrmin[23][46]=-1;
        $arrmin[23][47]=-3;
        $arrmin[23][48]=8;
        $arrmin[23][49]=4;
        $arrmin[23][50]=2;
        $arrmin[23][51]=0;
        $arrmin[23][52]=5;
        $arrmin[23][53]=6;
        $arrmin[23][54]=7;
        $arrmin[23][55]=4;
        $arrmin[23][56]=10;
        $arrmin[23][57]=7;
        $arrmin[23][58]=5;
        $arrmin[23][59]=6;

        return $arrmin;


      }

    }
?>
