<?php

namespace App\Http\Controllers;

use App\Models\cutCalendarQ;
use App\Models\incident;
use App\Models\ProgrammedTask;
use App\Models\TaskLog;
use App\Models\PrepayReportConfig;
use App\Models\User;
use App\Models\week_cut;
use App\SReport\SJourneyReport;
use App\STasks\SReportTasks;
use App\SUtils\SChecadorVsNominaUtils;
use App\SUtils\SPrepayrollUtils;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\SReportPayrollVSCap\SReportPVSCUtils;
use Log;

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

    public function testAdjustPost()
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer token'
        ];
        
        $client = new Client([
            'base_uri' => 'localhost:9091/cap/public/api/',
            'timeout' => 30.0,
            'headers' => $headers
        ]);

        $body = '{
            "dt_date": "2023-04-24",
            "minutes": "5",
            "comments": "Justificar 5 min de retardo",
            "adjust_type_id": 3,
            "employee_id": 5139,
            "ext_key": "106",
            "ext_sys": "pgh"
        }';
        
        try {
            $request = new \GuzzleHttp\Psr7\Request('POST', 'saveadjust', $headers, $body);
            $response = $client->sendAsync($request)->wait();
            dd($response);
        }
        catch (\Throwable $th) {
            dd($th);
        }
    }

    // public function testDelays(){
    //     //SReportPVSCUtils::delayProcess('2023-10-05', '2023-10-19', 1, [1212], 20);
    //     $oTask = ProgrammedTask::where('id_task', 165)->first();
    //     \App\SUtils\SChecadorVsNominaUtils::getReport($oTask->cfg, 'Q_104');
    //     //$config = '';
    //     //SReportPVSCUtils::manageTaskReport($oTask->cfg, 'Q_102');
        
    // }

    public function testDelays(){
        SReportPVSCUtils::delayProcess('2023-10-05', '2023-10-19', 1, [1212], 20);

        $config = "";

        SReportPVSCUtils::manageTaskReport($config,'Q_97');

        employeeController::deactivateUser(1020);
    }


    public function testShedulePrepayroll(){
        $lReports = PrepayReportConfig::where('is_delete', 0)
                                        ->orderBy('user_n_id', 'ASC')
                                        ->orderBy('order_vobo', 'ASC')
                                        ->get();

        foreach ($lReports as $oReport) {
            if ($oReport->since_date == null) {
                continue;
            }

            if ($oReport->is_biweek) {
                if ($oReport->until_date == null) {
                    $lQCuts = cutCalendarQ::where('dt_cut', '>=', $oReport->since_date);
                }
                else {
                    $lQCuts = cutCalendarQ::whereBetween('dt_cut', [$oReport->since_date, $oReport->until_date]);
                }

                $lQCuts = $lQCuts->where('is_delete', 0)
                                ->orderBy('dt_cut', 'ASC');

                $lQCuts = $lQCuts->get();

                if (empty($lQCuts)) {
                    Log::info('No se encontraron quincenas para el reporte: '.$oReport->id_configuration);
                    continue;
                }

                $oUser = User::find($oReport->user_n_id);
                $sMail = $oUser->email;
                if (!$sMail || empty($sMail)) {
                    Log::info('No se encontró el correo del usuario: '.$oUser->name);
                    continue;
                }

                $bDirect = false;
                $oDelegation = null;
                $lEmployees = SPrepayrollUtils::getEmployeesByUser($oReport->user_n_id, \SCons::PAY_W_Q, $bDirect, $oDelegation);

                if (empty($lEmployees)) {
                    Log::info('No se encontraron empleados para el usuario: '.$oUser->name);
                    continue;
                }

                // preparar json de configuración de reporte para comparar con las tareas programadas
                $oPrepayReportConfig = new \stdClass();
                $oPrepayReportConfig->pay_type = \SCons::PAY_W_Q;
                $oPrepayReportConfig->back_prepayroll = 0;
                $oPrepayReportConfig->companies = [];
                $oPrepayReportConfig->areas = [];
                $oPrepayReportConfig->departments_cap = [];
                $oPrepayReportConfig->departments_siie = [];
                $oPrepayReportConfig->benefit_policies = [];

                $oPrepayReportConfig->mails = (object) [
                    'to' => $sMail,
                    'cc' => '',
                    'cco' => 'edwin.carmona@swaplicado.com.mx'
                ];

                $oPrepayReportConfig->employees = $lEmployees;

                // preparar el filtro de las tareas programadas con ese ID de quincena
                $lProgrammedTasksQ = ProgrammedTask::where('task_type_id', \SCons::TASK_TYPE_REPORT_JOURNEY)
                                        ->where('is_delete', false)
                                        ->whereRaw('SUBSTRING(reference_id, 1, 1) = "Q"')
                                        ->where('execute_on', '>=', $oReport->since_date)
                                        ->get();

                foreach ($lQCuts as $oQCut) {
                    $isScheduled = false;
                    foreach ($lProgrammedTasksQ as $oTaskQ) {
                        $jsonTask = json_encode(json_decode($oTaskQ->cfg), JSON_PRETTY_PRINT);
                        $jsonReport = json_encode($oPrepayReportConfig, JSON_PRETTY_PRINT);
                        if ($jsonTask === $jsonReport && ('Q_'.$oQCut->id === $oTaskQ->reference_id)) {
                            $isScheduled = true;
                            Log::info('Tarea quincena previamente programada: '.$jsonReport);
                            break;
                        }
                    }

                    if (! $isScheduled ) {
                        $oTask = new ProgrammedTask();
                        $oTask->task_type_id = \SCons::TASK_TYPE_REPORT_JOURNEY;
                        $oTask->execute_on = Carbon::parse($oQCut->dt_cut)->addDay()->toDateString();
                        $oTask->apply_time = false;
                        $oTask->cfg = json_encode($oPrepayReportConfig, JSON_PRETTY_PRINT);
                        $oTask->reference_id = 'Q_'.$oQCut->id;
                        $oTask->is_done = false;
                        $oTask->is_delete = false;
                        $oTask->save();

                        Log::info('Tarea quincena programada: '.$oTask->id_task);
                    }
                }
            }
            else {
                if ($oReport->until_date == null) {
                    $lWeekCuts = week_cut::where('fin', '>=', $oReport->since_date);
                }
                else {
                    $lWeekCuts = week_cut::whereBetween('fin', [$oReport->since_date, $oReport->until_date]);
                }

                $lWeekCuts = $lWeekCuts->orderBy('fin', 'ASC');

                $lWeekCuts = $lWeekCuts->get();

                if (empty($lWeekCuts)) {
                    Log::info('No se encontraron cortes semanales para el reporte: '.$oReport->id_configuration);
                    continue;
                }

                $oUser = User::find($oReport->user_n_id);
                $sMail = $oUser->email;
                if (!$sMail || empty($sMail)) {
                    Log::info('No se encontró el correo del usuario: '.$oUser->name);
                    continue;
                }

                $bDirect = false;
                $oDelegation = null;
                $lEmployees = SPrepayrollUtils::getEmployeesByUser($oReport->user_n_id, \SCons::PAY_W_S, $bDirect, $oDelegation);

                if (empty($lEmployees)) {
                    Log::info('No se encontraron empleados para el usuario: '.$oUser->name);
                    continue;
                }

                // preparar json de configuración de reporte para comparar con las tareas programadas
                $oPrepayReportConfig = new \stdClass();
                $oPrepayReportConfig->pay_type = \SCons::PAY_W_S;
                $oPrepayReportConfig->back_prepayroll = 0;
                $oPrepayReportConfig->companies = [];
                $oPrepayReportConfig->areas = [];
                $oPrepayReportConfig->departments_cap = [];
                $oPrepayReportConfig->departments_siie = [];
                $oPrepayReportConfig->benefit_policies = [];

                $oPrepayReportConfig->mails = (object) [
                    'to' => $sMail,
                    'cc' => '',
                    'cco' => 'edwin.carmona@swaplicado.com.mx'
                ];
                $oPrepayReportConfig->employees = $lEmployees;
                // preparar el filtro de las tareas programadas con ese ID de quincena
                $lProgrammedTasksS = ProgrammedTask::where('task_type_id', \SCons::TASK_TYPE_REPORT_JOURNEY)
                                        ->where('is_delete', false)
                                        ->whereRaw('SUBSTRING(reference_id, 1, 1) = "S"')
                                        ->where('execute_on', '>=', $oReport->since_date)
                                        ->get();

                foreach ($lWeekCuts as $oWeekCut) {
                    $isScheduled = false;
                    foreach ($lProgrammedTasksS as $oTaskS) {
                        $jsonTask = json_encode(json_decode($oTaskS->cfg), JSON_PRETTY_PRINT);
                        $jsonReport = json_encode($oPrepayReportConfig, JSON_PRETTY_PRINT);
                        if ($jsonTask === $jsonReport && ('S_'.$oWeekCut->id === $oTaskS->reference_id)) {
                            $isScheduled = true;
                            Log::info('Tarea semana previamente programada: '.$oTaskS->id_task);
                            break;
                        }
                    }

                    if (! $isScheduled ) {
                        $oTask = new ProgrammedTask();
                        $oTask->task_type_id = \SCons::TASK_TYPE_REPORT_JOURNEY;
                        $oTask->execute_on = Carbon::parse($oWeekCut->fin)->addDay()->toDateString();
                        $oTask->apply_time = false;
                        $oTask->cfg = json_encode($oPrepayReportConfig, JSON_PRETTY_PRINT);
                        $oTask->reference_id = 'S_'.$oWeekCut->id;
                        $oTask->is_done = false;
                        $oTask->is_delete = false;
                        $oTask->save();

                        Log::info('Tarea quincena programada: '.$oTask->id_task);
                    }
                }
            }
                
        }
            
    }
}
