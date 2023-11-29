<?php namespace App\STasks;

use App\Models\cutCalendarQ;
use App\Models\ProgrammedTask;
use App\Models\week_cut;
use Carbon\Carbon;

class SReportTasks {

    /**
     * Programa los reportes configurados en el archivo tasks/report_journey_cfg.json
     * 
     * @return string con el error si es que lo hubo y cadena vacía si todo salió OK
     */
    public static function scheduleTasks()
    {
        if (config('app.env') !== 'production') {
            return "";
        }
        
        // Read File
        $jsonString = file_get_contents(base_path('tasks/report_journey_cfg.json'));
        $oReportCfg = json_decode($jsonString);

        try {
            \DB::beginTransaction();

            foreach ($oReportCfg->reports as $oReport) {
                $report_type = \SCons::TASK_TYPE_REPORT_JOURNEY;
                if(isset($oReport->configuration->report_type)){
                    $report_type = $oReport->configuration->report_type;
                }
                /**
                 * Programación de reportes quincenales
                 */
                if ($oReport->configuration->pay_type == \SCons::PAY_W_Q) {
                    // consultar fechas de corte para quincena
                    $lQCuts = cutCalendarQ::where('dt_cut', '>=', $oReport->since_date)
                                    ->where('is_delete', 0)
                                    ->orderBy('dt_cut', 'ASC');

                    $lQCuts = $lQCuts->get();

                    // preparar el filtro de las tareas programadas con ese ID de quincena
                    $lProgrammedTasksQ = ProgrammedTask::where('task_type_id', $report_type)
                                            ->where('is_delete', false)
                                            ->whereRaw('SUBSTRING(reference_id, 1, 1) = "Q"')
                                            ->where('execute_on', '>=', $oReport->since_date)
                                            ->get();

                    // programar una tarea por cada configuración de reporte
                    foreach ($lQCuts as $oQ) {
                        $isScheduled = false;
                        foreach ($lProgrammedTasksQ as $oTaskQ) {
                            $jsonTask = json_encode(json_decode($oTaskQ->cfg), JSON_PRETTY_PRINT);
                            $jsonReport = json_encode($oReport->configuration, JSON_PRETTY_PRINT);
                            if ($jsonTask === $jsonReport && ('Q_'.$oQ->id === $oTaskQ->reference_id)) {
                                $isScheduled = true;
                                break;
                            }
                        }

                        if (! $isScheduled ) {
                            $oTask = new ProgrammedTask();
                            if($report_type == \SCons::TASK_TYPE_REPORT_JOURNEY){
                                $oTask->execute_on = Carbon::parse($oQ->dt_cut)->addDay()->toDateString();
                                $oTask->apply_time = false;
                            }else if($report_type == \SCons::TASK_TYPE_REPORT_DG){
                                $day =(int) Carbon::parse($oQ->dt_cut)->format('d');
                                if($day > 15){
                                    $oTask->execute_on = Carbon::parse($oQ->dt_cut)->endOfMonth()->toDateString();  
                                }else{
                                    $oTask->execute_on = Carbon::parse($oQ->dt_cut)->setDay(15)->toDateString();
                                }
                                $oTask->apply_time = false;  
                            }else if($report_type == \SCons::TASK_TYPE_REPORT_CHECADOR_NOMINA){
                                $day =(int) Carbon::parse($oQ->dt_cut)->format('d');
                                if($day > 15){
                                    $oTask->execute_on = Carbon::parse($oQ->dt_cut)->endOfMonth()->addDay()->toDateString();  
                                }else{
                                    $oTask->execute_on = Carbon::parse($oQ->dt_cut)->setDay(16)->toDateString();
                                }
                                $oTask->apply_time = false;
                            }
                            
                            $oTask->cfg = json_encode($oReport->configuration, JSON_PRETTY_PRINT);
                            $oTask->reference_id = 'Q_'.$oQ->id;
                            $oTask->is_done = false;
                            $oTask->is_delete = false;
                            $oTask->task_type_id = $report_type;
    
                            $oTask->save();
                        }
                    }
                }
                /**
                 * Programación de reportes semanales
                 */
                else {
                    // consultar fechas de corte para semana
                    $lWCuts = week_cut::where('fin', '>=', $oReport->since_date);

                    // preparar el filtro de las tareas programadas con ese ID de semana
                    $lProgrammedTasksS = ProgrammedTask::where('task_type_id', $report_type)
                                            ->where('is_delete', false)
                                            ->whereRaw('SUBSTRING(reference_id, 1, 1) = "S"')
                                            ->where('execute_on', '>=', $oReport->since_date)
                                            ->get();

                    $lWCuts = $lWCuts->get();
                    
                    // programar una tarea por cada configuración de reporte
                    foreach ($lWCuts as $oS) {
                        $isScheduled = false;
                        foreach ($lProgrammedTasksS as $oTaskS) {
                            $jsonTask = json_encode(json_decode($oTaskS->cfg), JSON_PRETTY_PRINT);
                            $jsonReport = json_encode($oReport->configuration, JSON_PRETTY_PRINT);
                            if ($jsonTask === $jsonReport && ('S_'.$oS->id === $oTaskS->reference_id)) {
                                $isScheduled = true;
                                break;
                            }
                        }

                        if (! $isScheduled) {
                            $oTask = new ProgrammedTask();
                            $oTask->execute_on = Carbon::parse($oS->fin)->addDay()->toDateString();
                            $oTask->apply_time = false;
                            $oTask->cfg = json_encode($oReport->configuration, JSON_PRETTY_PRINT);
                            $oTask->reference_id = 'S_'.$oS->id;
                            $oTask->is_done = false;
                            $oTask->is_delete = false;
                            $oTask->task_type_id = $report_type;
    
                            $oTask->save();
                        }
                    }
                }
            }

            \DB::commit();

            return "";
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);

            return $th->getMessage();
        }
    }
}
