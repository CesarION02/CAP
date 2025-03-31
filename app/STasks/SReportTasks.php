<?php namespace App\STasks;

use App\Models\cutCalendarQ;
use App\Models\ProgrammedTask;
use App\Models\week_cut;
use App\Models\PrepayReportConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Clase SReportTasks
 * 
 * Esta clase se encarga de programar tareas relacionadas con reportes,
 * incluyendo reportes de pre-nómina y otros tipos de reportes configurados en el sistema.
 */
class SReportTasks {

    /**
     * Programa los reportes configurados en el archivo tasks/report_journey_cfg.json
     * y en la tabla PrepayReportConfig.
     * 
     * @return string Cadena vacía si todo salió bien, o un mensaje de error si ocurrió un problema.
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

            /**
             * ****************************************************************************************
             * Segunda parte: Programación de reportes desde PrepayReportConfig
             */
            
            $aPPConfigs = self::getSpecificPrepayrollConfigs();
            $lReportsBase = PrepayReportConfig::where('is_delete', 0)
                                ->where('is_report', 1)
                                ->orderBy('user_n_id', 'ASC')
                                ->orderBy('order_vobo', 'ASC');
            // Filtrar por configuraciones específicas
            if (count($aPPConfigs) > 0) {
                $lReports = (clone $lReportsBase)->whereIn('id_configuration', $aPPConfigs);
            }
            $lReports = $lReports->get();

            $sinceDatePrepayroll = self::getSinceDatePrepayroll();
            $reportType = \SCons::TASK_TYPE_REPORT_JOURNEY;
            foreach ($lReports as $oReport) {
                if (!$oReport->since_date) {
                    continue;
                }

                if ($oReport->is_biweek) {
                    self::schedulePrepayBiweeklyReports($oReport, $reportType, $sinceDatePrepayroll);
                } else {
                    self::schedulePrepayWeeklyReports($oReport, $reportType, $sinceDatePrepayroll);
                }
            }

            $aPPResumeConfigs = self::getSpecificPrepayrollResumeConfigs();
            if (count($aPPResumeConfigs) > 0) {
                $lResumeReports = (clone $lReportsBase)->whereIn('id_configuration', $aPPResumeConfigs);
            }
            $lResumeReports = $lResumeReports->get();

            $sinceDateResumePrepayroll = self::getSinceDateResumePrepayroll();

            DB::commit();
            return "";
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $th->getMessage();
        }
    }

    /**
     * Carga la configuración de reportes desde un archivo JSON.
     * 
     * @return object|null Objeto con la configuración de reportes o null si ocurre un error.
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
     * Carga la configuración de reporte resumen desde un archivo JSON.
     * 
     * @return object|null Objeto con la configuración de reportes o null si ocurre un error.
     */
    public static function loadReportResumeConfig()
    {
        $jsonPath = base_path('tasks/report_resume_cfg.json');
        if (!file_exists($jsonPath)) {
            Log::error("Archivo de configuración no encontrado: $jsonPath");
            return null;
        }

        $jsonString = file_get_contents($jsonPath);
        return json_decode($jsonString);
    }

    /**
     * Programa reportes quincenales basados en la configuración proporcionada.
     * 
     * @param object $oReport Configuración del reporte.
     * @param int $reportType Tipo de reporte.
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
     * Programa reportes semanales basados en la configuración proporcionada.
     * 
     * @param object $oReport Configuración del reporte.
     * @param int $reportType Tipo de reporte.
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
     * Obtiene las tareas programadas existentes para un tipo de reporte y prefijo.
     * 
     * @param int $reportType Tipo de reporte.
     * @param string $prefix Prefijo del identificador de referencia.
     * @param string $sinceDate Fecha inicial para filtrar tareas.
     * @return \Illuminate\Support\Collection Colección de tareas programadas.
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
     * 
     * @param \Illuminate\Support\Collection $lProgrammedTasks Tareas programadas existentes.
     * @param object $configuration Configuración del reporte.
     * @param string $referenceId Identificador de referencia de la tarea.
     * @return bool True si la tarea ya está programada, false en caso contrario.
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
     * 
     * @param string $dtCut Fecha de corte.
     * @param int $reportType Tipo de reporte.
     * @return string Fecha de ejecución calculada.
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
     * 
     * @param int $reportType Tipo de reporte.
     * @param string $executeOn Fecha de ejecución.
     * @param object $configuration Configuración del reporte.
     * @param string $referenceId Identificador de referencia.
     * @param int $priority Prioridad de la tarea.
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

    /**
     * Obtiene la fecha inicial para pre-nómina desde la configuración.
     * 
     * @return \Carbon\Carbon Fecha inicial configurada o el día anterior a la fecha actual.
     */
    private static function getSinceDatePrepayroll()
    {
        $oConfig = \App\SUtils\SConfiguration::getConfigurations();
        return $oConfig->sinceDatePrepayroll
            ? Carbon::parse($oConfig->sinceDatePrepayroll)
            : Carbon::now()->subDays(1);
    }

    /**
     * Obtiene la fecha inicial para pre-nómina desde la configuración.
     * 
     * @return \Carbon\Carbon Fecha inicial configurada o el día anterior a la fecha actual.
     */
    private static function getSinceDateResumePrepayroll()
    {
        $oConfig = \App\SUtils\SConfiguration::getConfigurations();
        return $oConfig->sinceDatePrepayrollResume
            ? Carbon::parse($oConfig->sinceDatePrepayrollResume)
            : Carbon::now()->subDays(1);
    }

    /**
     * Obtiene las configuraciones específicas de reportes de pre-nómina.
     * 
     * @return array Arreglo con los identificadores de configuración.
     */
    private static function getSpecificPrepayrollConfigs()
    {
        $oConfig = \App\SUtils\SConfiguration::getConfigurations();
        return $oConfig->onlyPrepayrollConfigsDelayReport;
    }

    /**
     * Obtiene las configuraciones específicas de reportes de resumen de pre-nómina e incidencias.
     * 
     * @return array Arreglo con los identificadores de configuración.
     */
    private static function getSpecificPrepayrollResumeConfigs()
    {
        $oConfig = \App\SUtils\SConfiguration::getConfigurations();
        return $oConfig->onlyPrepayrollConfigsResumeReport;
    }

    // Métodos auxiliares para la segunda parte

    /**
     * Programa reportes quincenales desde PrepayReportConfig.
     * 
     * @param object $oReport Configuración del reporte.
     * @param int $iReportType Tipo de reporte. (\SCons::TASK_TYPE_REPORT_JOURNEY, \SCons::TASK_TYPE_REPORT_PP_INCID_RESUME)
     * @param \Carbon\Carbon $sinceDatePrepayroll Fecha inicial para considerar reportes.
     */
    private static function schedulePrepayBiweeklyReports($oReport, $iReportType, $sinceDatePrepayroll)
    {
        $lQCutsBase = cutCalendarQ::where('dt_cut', '>=', $oReport->since_date);

        if ($oReport->until_date) {
            $lQCutsBase->whereBetween('dt_cut', [$oReport->since_date, $oReport->until_date]);
        }

        $lQCutsBase = $lQCutsBase->where('is_delete', 0);

        if ($iReportType == \SCons::TASK_TYPE_REPORT_JOURNEY) {
            $lQCuts = (clone $lQCutsBase)->where('dt_cut', '>', $sinceDatePrepayroll->toDateString())
                            ->where('dt_cut', '<=', '2025-04-04')
                            ->orderBy('dt_cut', 'ASC')
                            ->get();
    
            if ($lQCuts->isEmpty()) {
                Log::warning('No se encontraron quincenas para el reporte: ' . $oReport->id_configuration);
                return;
            }
            $oPrepayReportConfig = self::preparePrepayReportConfig($oReport, \SCons::PAY_W_Q);
        }
        if ($iReportType == \SCons::TASK_TYPE_REPORT_PP_INCID_RESUME) {
            $oReporConfigJson = self::loadReportResumeConfig();
            if (!$oReporConfigJson) {
                Log::error("Error al cargar la configuración de reportes de resumen.");
                return;
            }
            if (!$oReporConfigJson->reportQ) {
                Log::error('No se encontró la configuración de reporte de resumen quincenal: ' . $oReport->id_configuration);
                return;
            }
            if ($oReporConfigJson->reportQ->sinceDate && $oReporConfigJson->reportQ->untilDate) {
                $lQCuts = (clone $lQCutsBase)->where('dt_cut', '>=', $oReporConfigJson->reportQ->sinceDate)
                            ->where('dt_cut', '<=', $oReporConfigJson->reportQ->untilDate)
                            ->orderBy('dt_cut', 'ASC')
                            ->get();
            }
            else {
                $lQCuts = (clone $lQCutsBase)->where('dt_cut', '>=', $oReporConfigJson->reportQ->sinceDate)
                            ->where('dt_cut', '<=', '2025-04-04')
                            ->orderBy('dt_cut', 'ASC')
                            ->get();
            }
            if ($lQCuts->isEmpty()) {
                Log::error('No se encontraron quincenas para el reporte: ' . $oReport->id_configuration);
                return;
            }
            $oPrepayReportConfig = self::preparePrepayReportResumeConfig($oReport, $oReporConfigJson, \SCons::PAY_W_Q);
        }
        if (!$oPrepayReportConfig) {
            Log::warning('No se pudo preparar la configuración para el reporte quincenal: ' . $oReport->id_configuration);
            return;
        }
        $lProgrammedTasks = self::getProgrammedTasks($iReportType, 'Q', $oReport->since_date);

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
     * 
     * @param object $oReport Configuración del reporte.
     * @param \Carbon\Carbon $sinceDatePrepayroll Fecha inicial para considerar reportes.
     */
    private static function schedulePrepayWeeklyReports($oReport, $iReportType, $sinceDatePrepayroll)
    {
        $lWeekCuts = week_cut::where('fin', '>=', $oReport->since_date);

        if ($oReport->until_date) {
            $lWeekCuts->whereBetween('fin', [$oReport->since_date, $oReport->until_date]);
        }

        $lWeekCuts = $lWeekCuts->where('ini', '>=', $sinceDatePrepayroll->toDateString())
            ->where('fin', '<=', '2025-04-04')
            ->orderBy('fin', 'ASC')
            ->get();

        if ($lWeekCuts->isEmpty()) {
            Log::warning('No se encontraron cortes semanales para el reporte: ' . $oReport->id_configuration);
            return;
        }

        $oPrepayReportConfig = self::preparePrepayReportConfig($oReport, \SCons::PAY_W_S);
        if (!$oPrepayReportConfig) {
            Log::warning('No se pudo preparar la configuración para el reporte semanal: ' . $oReport->id_configuration);
            return;
        }
        $lProgrammedTasks = self::getProgrammedTasks($iReportType, 'S', $oReport->since_date);

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
     * 
     * @param object $oReport Configuración del reporte.
     * @param int $payType Tipo de pago (quincenal o semanal).
     * @return object|null Configuración del reporte preparada o null si ocurre un error.
     */
    private static function preparePrepayReportConfig($oReport, $payType)
    {
        $oUser = User::find($oReport->user_n_id);
        $sMail = $oUser->email ?? null;

        if (!$sMail) {
            Log::warning('No se encontró el correo del usuario: ' . $oUser->name);
            return null;
        }

        $bDirect = false;
        $oDelegation = null;
        $lEmployees = \App\SUtils\SPrepayrollUtils::getEmployeesByUser($oReport->user_n_id, $payType, $bDirect, $oDelegation);

        if (empty($lEmployees)) {
            Log::warning('No se encontraron empleados para el usuario: ' . $oUser->name);
            return null;
        }

        // si es un array asociativo:
        if (array_key_exists('0', $lEmployees)) {
            // transformar a un array simple
            $aux = [];
            foreach ($lEmployees as $key => $value) {
                $aux[] = $value;
            }
            $lEmployees = $aux;
        }

        $oPrepayReportConfig = new \stdClass();
        $oPrepayReportConfig->mails = (object) [
            'to' => $sMail,
            'cc' => '',
            'cco' => 'edwin.carmona@swaplicado.com.mx'
        ];
        $oPrepayReportConfig->employees = $lEmployees;
        $oPrepayReportConfig->pay_type = $payType;
        $oPrepayReportConfig->back_prepayroll = 0;
        $oPrepayReportConfig->companies = [];
        $oPrepayReportConfig->areas = [];
        $oPrepayReportConfig->departments_cap = [];
        $oPrepayReportConfig->departments_siie = [];
        $oPrepayReportConfig->benefit_policies = [];

        return $oPrepayReportConfig;
    }

    public static function preparePrepayReportResumeConfig($oReportTableConfig, $oReporConfigJson, $payType)
    {
        $oUser = User::find($oReportTableConfig->user_n_id);
        $sMail = $oUser->email ?? null;

        if (!$sMail) {
            Log::warning('No se encontró el correo del usuario: ' . $oUser->name);
            return null;
        }

        $bDirect = false;
        $oDelegation = null;
        $lEmployees = \App\SUtils\SPrepayrollUtils::getEmployeesByUser($oReportTableConfig->user_n_id, $payType, $bDirect, $oDelegation);

        if (empty($lEmployees)) {
            Log::warning('No se encontraron empleados para el usuario: ' . $oUser->name);
            return null;
        }

        if (!$oReporConfigJson->reportQ->incidentTypes) {
            Log::warning('No se encontró la configuración de incidencias para el reporte: ' . $oReportTableConfig->id_configuration);
            return null;
        }

        if (!is_array($oReporConfigJson->reportQ->incidentTypes)) {
            Log::warning('La configuración de incidencias en el reporte quincenal resumen no es un array.');
            return null;
        }

        // si es un array asociativo:
        if (array_key_exists('0', $lEmployees)) {
            // transformar a un array simple
            $aux = [];
            foreach ($lEmployees as $key => $value) {
                $aux[] = $value;
            }
            $lEmployees = $aux;
        }

        $oPrepayReportConfig = new \stdClass();
        $oPrepayReportConfig->mails = (object) [
            'to' => $sMail,
            'cc' => '',
            'cco' => 'edwin.carmona@swaplicado.com.mx'
        ];
        $oPrepayReportConfig->employees = $lEmployees;
        $oPrepayReportConfig->pay_type = $payType;
        $oPrepayReportConfig->incident_types = $oReporConfigJson->reportQ->incidentTypes;
        $oPrepayReportConfig->days_ago = $oReporConfigJson->reportQ->daysAgo;
        $oPrepayReportConfig->back_prepayroll = 0;
        $oPrepayReportConfig->companies = [];
        $oPrepayReportConfig->areas = [];
        $oPrepayReportConfig->departments_cap = [];
        $oPrepayReportConfig->departments_siie = [];
        $oPrepayReportConfig->benefit_policies = [];

        return $oPrepayReportConfig;
    }
}
