<?php

namespace App\Http\Controllers;

use App\Models\cutCalendarQ;
use App\Models\incident;
use App\Models\ProgrammedTask;
use App\Models\TaskLog;
use App\Models\week_cut;
use App\SReport\SJourneyReport;
use App\STasks\SReportTasks;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
                    return SJourneyReport::manageTaskReport($oTask->cfg, $oTask->reference_id);

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
        SReportTasks::scheduleTasks();
    }

    public function reSaveDays(Request $request)
    {
        $lIncidents = incident::where('start_date', '>=', '2023-01-01')
                                // ->where('id', 7569)
                                ->where('is_external', false)
                                ->get();

        $oCont = new incidentController();
        foreach ($lIncidents as $oIncident) {
            if (! $oIncident->is_external) {
                switch ($oIncident->type_incidents_id) {
                    case \SCons::INC_TYPE['INA_S_PER']:
                    case \SCons::INC_TYPE['INA_C_PER_SG']:
                    case \SCons::INC_TYPE['INA_C_PER_CG']:
                    case \SCons::INC_TYPE['INA_AD_REL_CH']:
                    case \SCons::INC_TYPE['INA_AD_SUSP']:
                    case \SCons::INC_TYPE['INA_AD_OT']:
                    case \SCons::INC_TYPE['ONOM_EXT']:
                    case \SCons::INC_TYPE['RIESGO']:
                    case \SCons::INC_TYPE['CAPACIT']:
                    case \SCons::INC_TYPE['TRAB_F_PL']:
                    case \SCons::INC_TYPE['DIA_OTOR']:
                    case \SCons::INC_TYPE['DESCANSO']:
                    case \SCons::INC_TYPE['INA_TR_F_PL']:
                    case \SCons::INC_TYPE['ONOM_CAP']:
                    case \SCons::INC_TYPE['PERM']:
                        // determina los días efectivos de la incidencia
                        $oIncident->eff_day = Carbon::parse($oIncident->start_date)->diffInDays(Carbon::parse($oIncident->end_date)) + 1;
                        $oIncident->cls_inc_id = 1;
                        break;
                    case \SCons::INC_TYPE['ENFERMEDAD']:
                    case \SCons::INC_TYPE['MATER']:
                    case \SCons::INC_TYPE['LIC_CUIDADOS']:
                    case \SCons::INC_TYPE['PATER']:
                    case \SCons::INC_TYPE['INC_CAP']:
                    case \SCons::INC_TYPE['INA_PRES_MED']:
                        $oIncident->eff_day = Carbon::parse($oIncident->start_date)->diffInDays(Carbon::parse($oIncident->end_date)) + 1;
                        $oIncident->cls_inc_id = 2;
                        break;
    
                    case \SCons::INC_TYPE['VAC']:
                    case \SCons::INC_TYPE['VAC_CAP']:
                    case \SCons::INC_TYPE['VAC_PEND']:
                        $oIncident->cls_inc_id = 3;
                        break;
                    
                    default:
                        # code...
                        break;
                }

                $oIncident->save();
                $oCont->saveDays($oIncident);
            }
        }
    }
}
