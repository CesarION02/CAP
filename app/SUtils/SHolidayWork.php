<?php namespace App\SUtils;
use DataTime;
use Carbon\Carbon;
use DB;
use App\SUtils\SDateTimeUtils;
use App\SUtils\SRegistryRow;
use App\SData\SDataProcess;
use App\SUtils\SDateUtils;
use App\Models\processed_data;
use App\Models\period_processed;
use App\Models\holidayworked;
use App\Models\incident;

class SHolidayWork{

    public static function holidayWorked($sStartDate, $sEndDate)
    {
        $lRows = DB::table('processed_data')
        ->where(function($query) use ($sStartDate,$sEndDate) {
            $query->whereBetween('inDate',[$sStartDate,$sEndDate])
            ->OrwhereBetween('outDate',[$sStartDate,$sEndDate]);
        })
        ->where('is_holiday',1)
        ->where('haschecks',1)
        ->orderBy('inDate')
        ->get();  

        for ( $i = 0 ; count($lRows) > $i ; $i++ ) {
        

                //sacar día festivo a ingresar 
                $holiday = DB::table('holidays')
                            ->where('fecha','=',$lRows[$i]->outDate)
                            ->get();
                if(count($holiday) < 1){
                //revisar si ya esta el día festivo trabajado para este empleado
                    $holidaywork = DB::table('holiday_worked')
                                ->where('employee_id',$lRows[$i]->employee_id)
                                ->where('holiday_id',$holiday[0]->id)
                                ->get();
                    //si esta el día festivo ya no se ingresa
                    if( count($holidaywork) == 0 ){
                    //insertar día festivo trabajado por un empleado
                        $holidaywork = new holidayworked();
                        $holidaywork->employee_id = $lRows[$i]->employee_id;
                        $holidaywork->holiday_id = $holiday[0]->id;
                        $holidaywork->save();

                        //insertar numero de incidentes que se necesitaran dependiendo del archivo de configuración.
                        $config = \App\SUtils\SConfiguration::getConfigurations();
                        for( $j = 0 ; $config->days_granted > $j ; $j++ ){
                            $incident = new incident();
                            $incident->external_key = "0_0";
                            $incident->num = 0;
                            $incident->type_incidents_id = 17;
                            $incident->cls_inc_id = 1;
                            $incident->start_date = '0000-00-00';
                            $incident->end_date = '0000-00-00';
                            $incident->eff_day = 0;
                            $incident->ben_year = 0;
                            $incident->employee_id = $lRows[$i]->employee_id;

                            $incident->created_by = session()->get('user_id');
                            $incident->updated_by = session()->get('user_id');

                            $incident->holiday_worked_id = $holidaywork->id;
                            $incident->save();
                        }
                    }
                }

             
        }
    }

}
?>