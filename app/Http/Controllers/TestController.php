<?php

namespace App\Http\Controllers;

use App\Models\cutCalendarQ;
use App\Models\ProgrammedTask;
use App\Models\TaskLog;
use App\Models\week_cut;
use App\SReport\SJourneyReport;
use Carbon\Carbon;

class TestController extends Controller
{
    public function report()
    {
        $oCurrentDateTime = Carbon::now();
        $lPendingTasks = ProgrammedTask::where('is_done', false)
                                ->where('is_delete', 0)
                                ->where('execute_on', '<=', $oCurrentDateTime->toDateString())
                                ->orderBy('execute_on', 'ASC')
                                ->orderBy('execute_at', 'ASC')
                                ->get();

        foreach ($lPendingTasks as $oTask) {
            $oTaskLog = new TaskLog();
            $oTaskLog->status = 'iniciada';
            $oTaskLog->cur_cfg = $oTask->cfg;
            $oTaskLog->log_message = "";
            $oTaskLog->task_id = $oTask->id_task;
            $oTaskLog->save();

            if ($oTask->apply_time) {
                $oTaskDateTime = Carbon::parse($oTask->execute_on.' '.$oTask->execute_at);
                if ($oTaskDateTime->greaterThan($oCurrentDateTime)) {
                    $oTaskLog = new TaskLog();
                    $oTaskLog->status = 'descartada';
                    $oTaskLog->cur_cfg = $oTask->cfg;
                    $oTaskLog->log_message = "Fuera de horario.";
                    $oTaskLog->task_id = $oTask->id_task;
                    $oTaskLog->save();

                    continue;
                }
            }

            switch ($oTask->task_type_id) {
                case \SCons::TASK_TYPE_REPORT_JOURNEY:
                    $response = SJourneyReport::manageTaskReport($oTask->cfg, $oTask->reference_id);
                    break;

                default:
                    $response = "Tipo de tarea desconocido.";
                    break;
            }

            if (strlen($response) == 0) {
                $oTask->is_done = true;
                $oTask->done_at = date("Y-m-d h:i:sa");
                $oTask->save();

                $oTaskLog = new TaskLog();
                $oTaskLog->status = 'terminada';
                $oTaskLog->cur_cfg = $oTask->cfg;
                $oTaskLog->log_message = "";
                $oTaskLog->task_id = $oTask->id_task;
                $oTaskLog->save();
            }
            else {
                $oTaskLog = new TaskLog();
                $oTaskLog->status = 'error';
                $oTaskLog->cur_cfg = $oTask->cfg;
                $oTaskLog->log_message = $response;
                $oTaskLog->task_id = $oTask->id_task;
                $oTaskLog->save();
            }
        }
    }

    public function scheduleTasks()
    {
        // Read File
        $jsonString = file_get_contents(base_path('tasks/report_journey_cfg.json'));
        $oReportCfg = json_decode($jsonString);

        try {
            \DB::beginTransaction();

            foreach ($oReportCfg->reports as $oReport) {
                /**
                 * Programación de reportes quincenales
                 */
                if ($oReport->configuration->pay_type == \SCons::PAY_W_Q) {
                    // consultar fechas de corte para quincena
                    $lQCuts = cutCalendarQ::where('dt_cut', '>=', $oReport->since_date)
                                    ->where('is_delete', 0)
                                    ->orderBy('dt_cut', 'ASC');

                    $lQCuts = $lQCuts->get();
            
                    
                    // if (count($lProgrammedTasks) > 0) {
                    //     $sTasks = implode("','", $lProgrammedTasks);
                    //     $whereRaw = "CONCAT('Q', '_', id) NOT IN ('" . $sTasks . "')";
                    //     $lQCuts = $lQCuts->whereRaw($whereRaw);
                    // }

                    // \DB::enableQueryLog();

                    // preparar el filtro de las tareas programadas con ese número de quincena
                    $lProgrammedTasksQ = ProgrammedTask::where('task_type_id', \SCons::TASK_TYPE_REPORT_JOURNEY)
                                            ->where('is_delete', false)
                                            ->whereRaw('SUBSTRING(reference_id, 1, 1) = "Q"')
                                            ->where('execute_on', '>=', $oReport->since_date)
                                            ->get();

                    // dd(\DB::getQueryLog());

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
                            $oTask->execute_on = Carbon::parse($oQ->dt_cut)->addDay()->toDateString();
                            $oTask->apply_time = false;
                            $oTask->cfg = json_encode($oReport->configuration, JSON_PRETTY_PRINT);
                            $oTask->reference_id = 'Q_'.$oQ->id;
                            $oTask->is_done = false;
                            $oTask->is_delete = false;
                            $oTask->task_type_id = \SCons::TASK_TYPE_REPORT_JOURNEY;
    
                            $oTask->save();
                        }
                    }
                }

                /**
                 * Programación de reportes semanales
                 */else {
                    // consultar fechas de corte para semana
                    $lWCuts = week_cut::where('fin', '>=', $oReport->since_date);

                    // preparar el filtro de las tareas programadas con ese número de semana
                    // preparar el filtro de las tareas programadas con ese número de quincena
                    $lProgrammedTasksS = ProgrammedTask::where('task_type_id', \SCons::TASK_TYPE_REPORT_JOURNEY)
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
                            $oTask->task_type_id = \SCons::TASK_TYPE_REPORT_JOURNEY;
    
                            $oTask->save();
                        }
                    }
                }
            }

            \DB::commit();
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            return $th->getMessage();
        }
    }
}
