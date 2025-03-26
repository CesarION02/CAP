<?php namespace App\STasks;

use App\Models\cutCalendarQ;
use App\Models\ProgrammedTask;
use App\Models\week_cut;
use App\Models\PrepayReportConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Primera parte: Programación de reportes desde report_journey_cfg.json
        $oReportCfg = self::loadReportConfig();
        if (!$oReportCfg) {
            return "Error al cargar la configuración de reportes.";
        }

        try {
            DB::beginTransaction();

            foreach ($oReportCfg->reports as $oReport) {
                $reportType = $oReport->configuration->report_type ?? \SCons::TASK_TYPE_REPORT_JOURNEY;

                if ($oReport->configuration->pay_type == \SCons::PAY_W_Q) {
                    self::scheduleBiweeklyReports($oReport, $reportType);
                } else {
                    self::scheduleWeeklyReports($oReport, $reportType);
                }
            }

            // Segunda parte: Programación de reportes desde PrepayReportConfig
            $lReports = PrepayReportConfig::where('is_delete', 0)
                ->orderBy('user_n_id', 'ASC')
                ->orderBy('order_vobo', 'ASC')
                ->get();

            $sinceDatePrepayroll = self::getSinceDatePrepayroll();

            foreach ($lReports as $oReport) {
                if (!$oReport->since_date) {
                    continue;
                }

                if ($oReport->is_biweek) {
                    self::schedulePrepayBiweeklyReports($oReport, $sinceDatePrepayroll);
                } else {
                    self::schedulePrepayWeeklyReports($oReport, $sinceDatePrepayroll);
                }
            }

            DB::commit();
            return "";
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $th->getMessage();
        }
    }

    /**
     * Carga la configuración de reportes desde el archivo JSON.
     */
    private static function loadReportConfig()
    {
        $jsonPath = base_path('tasks/report_journey_cfg.json');
        if (!file_exists($jsonPath)) {
            Log::error("Archivo de configuración no encontrado: $jsonPath");
            return null;
        }

        $jsonString = file_get_contents($jsonPath);
        return json_decode($jsonString);
    }

    /**
     * Programa reportes quincenales.
     */
    private static function scheduleBiweeklyReports($oReport, $reportType)
    {
        $lQCuts = cutCalendarQ::where('dt_cut', '>=', $oReport->since_date)
            ->where('is_delete', 0)
            ->orderBy('dt_cut', 'ASC')
            ->get();

        $lProgrammedTasks = self::getProgrammedTasks($reportType, 'Q', $oReport->since_date);

        $priority = 0;
        foreach ($lQCuts as $oQ) {
            if (!self::isTaskScheduled($lProgrammedTasks, $oReport->configuration, 'Q_' . $oQ->id)) {
                $executeOn = self::calculateBiweeklyExecutionDate($oQ->dt_cut, $reportType);
                self::createTask($reportType, $executeOn, $oReport->configuration, 'Q_' . $oQ->id, $priority);
            }
        }
    }

    /**
     * Programa reportes semanales.
     */
    private static function scheduleWeeklyReports($oReport, $reportType)
    {
        $lWCuts = week_cut::where('fin', '>=', $oReport->since_date)
            ->orderBy('fin', 'ASC')
            ->get();

        $lProgrammedTasks = self::getProgrammedTasks($reportType, 'S', $oReport->since_date);

        $priority = 0;
        foreach ($lWCuts as $oS) {
            if (!self::isTaskScheduled($lProgrammedTasks, $oReport->configuration, 'S_' . $oS->id)) {
                $executeOn = Carbon::parse($oS->fin)->addDay()->toDateString();
                self::createTask($reportType, $executeOn, $oReport->configuration, 'S_' . $oS->id, $priority);
            }
        }
    }

    /**
     * Obtiene las tareas programadas existentes.
     */
    private static function getProgrammedTasks($reportType, $prefix, $sinceDate)
    {
        return ProgrammedTask::where('task_type_id', $reportType)
            ->where('is_delete', false)
            ->whereRaw('SUBSTRING(reference_id, 1, 1) = ?', [$prefix])
            ->where('execute_on', '>=', $sinceDate)
            ->orderBy('execute_on', 'ASC')
            ->orderBy('priority', 'ASC')
            ->get();
    }

    /**
     * Verifica si una tarea ya está programada.
     */
    private static function isTaskScheduled($lProgrammedTasks, $configuration, $referenceId)
    {
        $jsonReport = json_encode($configuration, JSON_PRETTY_PRINT);

        foreach ($lProgrammedTasks as $oTask) {
            $jsonTask = json_encode(json_decode($oTask->cfg), JSON_PRETTY_PRINT);
            if ($jsonTask === $jsonReport && $oTask->reference_id === $referenceId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcula la fecha de ejecución para reportes quincenales.
     */
    private static function calculateBiweeklyExecutionDate($dtCut, $reportType)
    {
        $day = (int) Carbon::parse($dtCut)->format('d');

        if ($reportType == \SCons::TASK_TYPE_REPORT_DG) {
            return $day > 15
                ? Carbon::parse($dtCut)->endOfMonth()->toDateString()
                : Carbon::parse($dtCut)->setDay(15)->toDateString();
        }

        if ($reportType == \SCons::TASK_TYPE_REPORT_CHECADOR_NOMINA) {
            return $day > 15
                ? Carbon::parse($dtCut)->endOfMonth()->addDay()->toDateString()
                : Carbon::parse($dtCut)->setDay(16)->toDateString();
        }

        return Carbon::parse($dtCut)->addDay()->toDateString();
    }

    /**
     * Crea una nueva tarea programada.
     */
    private static function createTask($reportType, $executeOn, $configuration, $referenceId, $priority = 0)
    {
        $oTask = new ProgrammedTask();
        $oTask->task_type_id = $reportType;
        $oTask->execute_on = $executeOn;
        $oTask->apply_time = false;
        $oTask->priority = $priority;
        $oTask->cfg = json_encode($configuration, JSON_PRETTY_PRINT);
        $oTask->reference_id = $referenceId;
        $oTask->is_done = false;
        $oTask->is_delete = false;
        $oTask->save();
    }

    // Métodos auxiliares para la segunda parte

    /**
     * Obtiene la fecha inicial para pre-nómina desde la configuración.
     */
    private static function getSinceDatePrepayroll()
    {
        $oConfig = \App\SUtils\SConfiguration::getConfigurations();
        return $oConfig->sinceDatePrepayroll
            ? Carbon::parse($oConfig->sinceDatePrepayroll)
            : Carbon::now()->subDays(1);
    }

    /**
     * Programa reportes quincenales desde PrepayReportConfig.
     */
    private static function schedulePrepayBiweeklyReports($oReport, $sinceDatePrepayroll)
    {
        $lQCuts = cutCalendarQ::where('dt_cut', '>=', $oReport->since_date);

        if ($oReport->until_date) {
            $lQCuts->whereBetween('dt_cut', [$oReport->since_date, $oReport->until_date]);
        }

        $lQCuts = $lQCuts->where('is_delete', 0)
                        ->where('dt_cut', '>', $sinceDatePrepayroll->toDateString())
                        ->orderBy('dt_cut', 'ASC')
                        ->get();

        if ($lQCuts->isEmpty()) {
            Log::info('No se encontraron quincenas para el reporte: ' . $oReport->id_configuration);
            return;
        }

        $oPrepayReportConfig = self::preparePrepayReportConfig($oReport, \SCons::PAY_W_Q);
        $lProgrammedTasks = self::getProgrammedTasks(\SCons::TASK_TYPE_REPORT_JOURNEY, 'Q', $oReport->since_date);

        $priority = 2;
        foreach ($lQCuts as $oQCut) {
            if (!self::isTaskScheduled($lProgrammedTasks, $oPrepayReportConfig, 'Q_' . $oQCut->id)) {
                $executeOn = Carbon::parse($oQCut->dt_cut)->addDay()->toDateString();
                self::createTask(\SCons::TASK_TYPE_REPORT_JOURNEY, $executeOn, $oPrepayReportConfig, 'Q_' . $oQCut->id, $priority);
                Log::info('Tarea quincena programada: Q_' . $oQCut->id);
            }
        }
    }

    /**
     * Programa reportes semanales desde PrepayReportConfig.
     */
    private static function schedulePrepayWeeklyReports($oReport, $sinceDatePrepayroll)
    {
        $lWeekCuts = week_cut::where('fin', '>=', $oReport->since_date);

        if ($oReport->until_date) {
            $lWeekCuts->whereBetween('fin', [$oReport->since_date, $oReport->until_date]);
        }

        $lWeekCuts = $lWeekCuts->where('inicio', '>=', $sinceDatePrepayroll->toDateString())
            ->orderBy('fin', 'ASC')
            ->get();

        if ($lWeekCuts->isEmpty()) {
            Log::info('No se encontraron cortes semanales para el reporte: ' . $oReport->id_configuration);
            return;
        }

        $oPrepayReportConfig = self::preparePrepayReportConfig($oReport, \SCons::PAY_W_S);
        $lProgrammedTasks = self::getProgrammedTasks(\SCons::TASK_TYPE_REPORT_JOURNEY, 'S', $oReport->since_date);

        $priority = 2;
        foreach ($lWeekCuts as $oWeekCut) {
            if (!self::isTaskScheduled($lProgrammedTasks, $oPrepayReportConfig, 'S_' . $oWeekCut->id)) {
                $executeOn = Carbon::parse($oWeekCut->fin)->addDay()->toDateString();
                self::createTask(\SCons::TASK_TYPE_REPORT_JOURNEY, $executeOn, $oPrepayReportConfig, 'S_' . $oWeekCut->id, $priority);
                Log::info('Tarea semana programada: S_' . $oWeekCut->id);
            }
        }
    }

    /**
     * Prepara la configuración del reporte para PrepayReportConfig.
     */
    private static function preparePrepayReportConfig($oReport, $payType)
    {
        $oUser = User::find($oReport->user_n_id);
        $sMail = $oUser->email ?? null;

        if (!$sMail) {
            Log::info('No se encontró el correo del usuario: ' . $oUser->name);
            return null;
        }

        $bDirect = false;
        $oDelegation = null;
        $lEmployees = \App\SUtils\SPrepayrollUtils::getEmployeesByUser($oReport->user_n_id, $payType, $bDirect, $oDelegation);

        if (empty($lEmployees)) {
            Log::info('No se encontraron empleados para el usuario: ' . $oUser->name);
            return null;
        }

        $oPrepayReportConfig = new \stdClass();
        $oPrepayReportConfig->pay_type = $payType;
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

        return $oPrepayReportConfig;
    }
}
