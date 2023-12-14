<?php

namespace App\Console\Commands;

use App\Http\Controllers\SyncController;
use App\Models\ProgrammedTask;
use App\Models\TaskLog;
use App\SReport\SJourneyReport;
use App\STasks\SAdjustsPgh;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\SUtils\SChecadorVsNominaUtils;
use App\SReportPayrollVSCap\SReportPVSCUtils;

class TaskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:execute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta las tareas programadas en la tabla tasks de la BD';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
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
                /**
                 * Reportes programados de prenómina
                 */
                case \SCons::TASK_TYPE_REPORT_JOURNEY:
                    $response = SJourneyReport::manageTaskReport($oTask->cfg, $oTask->reference_id);
                    break;

                /**
                 * Inserción de ajustes pendientes desde PGH
                 */
                case \SCons::TASK_TYPE_ADJUST_PGH:
                    $response = SAdjustsPgh::processAdjustTask($oTask->cfg);
                    break;

                case \SCons::TASK_TYPE_REPORT_CHECADOR_NOMINA:
                    $response = SChecadorVsNominaUtils::getReport($oTask->cfg, $oTask->reference_id);
                    break;

                /** 
                 * Inserción de datos de reporte DG
                */
                case \SCons::TASK_TYPE_REPORT_DG:
                    $response = SReportPVSCUtils::manageTaskReport($oTask->cfg, $oTask->reference_id);
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
}
