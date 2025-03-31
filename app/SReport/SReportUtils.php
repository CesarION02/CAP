<?php namespace App\SReport;

use App\Models\cutCalendarQ;
use App\Models\week_cut;
use Carbon\Carbon;
use App\Models\incident;
use App\Models\incidentDay;
use Log;

class SReportUtils
{
    /**
     * Obtiene la colección de datos del reporte programado
     * 
     * @param string $sConfiguration
     * @param string $sReference
     * @return array|string
     */
    public static function getJourneyData($sConfiguration, $sReference) {
        try {
            $oConfiguration = SReportUtils::getReportConfigObj($sConfiguration);
            if (is_string($oConfiguration)) {
                return $oConfiguration;
            }

            // Obtener la fecha de inicio y fin del reporte
            $aDates = SReportUtils::getStartAndEndDate($oConfiguration, $sReference);
            if (is_string($aDates)) {
                return $aDates;
            }
            $sStartDate = $aDates[0];
            $sEndDate = $aDates[1];

            $lData = SJourneyReport::getJourneyData($sStartDate, $sEndDate, $oConfiguration->pay_type, 
                                                        $oConfiguration->companies, 
                                                        $oConfiguration->areas, 
                                                        $oConfiguration->departments_cap, 
                                                        $oConfiguration->departments_siie, 
                                                        $oConfiguration->employees, 
                                                        $oConfiguration->benefit_policies);

            
            return $lData;
        }
        catch (\Exception $e) {
            return "Error, no se pudo obtener la información del reporte programado. " . $e->getMessage();
        }
    }

    /**
     * Obtiene el objeto de configuración del reporte programado
     * 
     * @param mixed $sConfiguration
     * @return object|string
     */
    public static function getReportConfigObj($sConfiguration) {
        // Validar si la cadena recibida es un JSON
        if (! SJourneyReport::isJson($sConfiguration)) {
            return "Error, la configuración recibida no es un string JSON.";
        }

        $oConfiguration = json_decode($sConfiguration);

        // Si la configuración de tipo de pago no es correcta, retorna error
        if ($oConfiguration->pay_type == 0 || $oConfiguration->pay_type == "") {
            return "Error, el tipo de pago en la configuración no es válido.";
        }

        if (strlen($oConfiguration->mails->to) == 0) {
            return "Error, los destinarios para el correo no son válidos.";
        }

        return $oConfiguration;
    }

    /**
     * Obtiene la fecha de inicio y fin del reporte programado
     *
     * @param object $oConfiguration
     * @param string $sReference
     * @return array|string
     */
    public static function getStartAndEndDate($oConfiguration, $sReference) {
        $sStartDate = "";
        $sEndDate = "";
        try {
            // La referencia es un string con el tipo de pago _ id de corte (Ejem: Q_456)
            $numPP = substr($sReference, 2);
            if ($oConfiguration->pay_type == \SCons::PAY_W_Q) {
                $oCut = cutCalendarQ::find($numPP);
                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte con la referencia: " . $sReference;
                }
                $oDate = Carbon::parse($oCut->dt_cut);
                if ($oConfiguration->back_prepayroll > 0) {
                    $oDate->subDays(15 * $oConfiguration->back_prepayroll);
                }

                $lCuts = cutCalendarQ::where('dt_cut', '<=', $oDate->toDateString())
                                        ->where('is_delete', 0)
                                        ->orderBy('dt_cut', 'DESC')
                                        ->limit(2)
                                        ->get();

                if (count($lCuts) < 2) {
                    return "Error, no se encontró fecha de corte para el reporte programado.";
                }

                $sEndDate = $lCuts[0]->dt_cut;
                $sStartDate = Carbon::parse($lCuts[1]->dt_cut)->addDay()->toDateString();
                $sPayTypeText = "Quincena";
            }
            else {
                $oCut = week_cut::find($numPP);
                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte con la referencia: " . $sReference;
                }
                $oDate = Carbon::parse($oCut->fin);
                if ($oConfiguration->back_prepayroll > 0) {
                    $oDate->subDays(7 * $oConfiguration->back_prepayroll);
                }

                $oCut = week_cut::where('fin', '<=', $oDate->toDateString())
                            ->orderBy('fin', 'DESC')
                            ->first();

                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte para el reporte programado.";
                }

                $sStartDate = $oCut->ini;
                $sEndDate = $oCut->fin;
            }

            return array($sStartDate, $sEndDate);
        }
        catch (\Exception $e) {
            return "Error, no se pudo obtener la información del reporte programado. " . $e->getMessage();
        }
    }

    /**
     * Obtiene el texto del tipo de pago
     *
     * @param string $sPayType
     * @return string
     */
    public static function getPayTypeText($sPayType) {
        switch ($sPayType) {
            case \SCons::PAY_W_Q:
                return "Quincena";
            case \SCons::PAY_W_S:
                return "Semana";
            default:
                return "Error, el tipo de pago no es válido.";
        }
    }

    /**
     * Obtiene las columnas del reporte programado
     *
     * @param object $oConfiguration
     * @return array|null
     */
    public static function getColumns($oConfiguration) {
        $aColumns = null;
        if (isset($oConfiguration->order_columns)) {
            $aColumns = $oConfiguration->order_columns;
        }

        return $aColumns;
    }

    /**
     * Agrega las incidencias de los empleados dentro del periodo configurado en report_resume_cfg.json
     * 
     * @param mixed $lDataReceived
     * @param mixed $oConfiguration
     * @return mixed $lData
     */
    public static function addIncidentsResume($lDataReceived, $oConfiguration) {
        // Clonar o copiar arreglo de datos $lDataReceived
        $lData = array_map(fn($item) => clone $item, $lDataReceived);
    
        $startDate = Carbon::now()->subDays($oConfiguration->days_ago)->toDateString();
        $endDate = Carbon::now()->toDateString();
    
        // Consultas base para las incidencias por empleado
        $incidentBaseQuery = function ($employeeId) use ($startDate, $endDate) {
            return incident::where('employee_id', $employeeId)
                ->where('is_delete', 0)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate]);
                });
        };
    
        foreach ($lData as $oEmpData) {
            $oEmpData->aIncidents = [];
    
            // Pre-cargar todos los tipos de incidentes en un solo paso
            $incidentTypes = \SCons::INC_TYPE;
            $incidentsGrouped = [];
    
            foreach ($incidentTypes as $incidentTypeKey => $incidentTypeValue) {
                // Pre-cargar todas las incidencias para el tipo de incidencia
                $incidentsGrouped[$incidentTypeKey] = $incidentBaseQuery($oEmpData->idEmployee)
                    ->where('type_incidents_id', $incidentTypeValue)
                    ->get();
            }
    
            foreach ($oConfiguration->incident_types as $incidentType) {
                $oResume = new \stdClass();
                $oResume->text = "";
                $oResume->unit = "días";
                $oResume->counter = 0;
                $oResume->lDays = [];
    
                switch ($incidentType) {
                    case \SCons::INC_TYPE['INA_S_PER']:
                        $oResume->text = "Inasistencia sin permiso";
                        $oResume->counter = $incidentsGrouped['INA_S_PER']->count();
                        break;
    
                    case \SCons::INC_TYPE['INA_C_PER_SG']:
                        $oResume->text = "Inasistencia con permiso sin goce de sueldo";
                        $oResume->counter = $incidentsGrouped['INA_C_PER_SG']->count();
                        break;
    
                    case \SCons::INC_TYPE['INA_C_PER_CG']:
                        $oResume->text = "Inasistencia con permiso con goce de sueldo";
                        $oResume->counter = $incidentsGrouped['INA_C_PER_CG']->count();
                        break;
    
                    case \SCons::INC_TYPE['INA_AD_REL_CH']:
                        $oResume->text = "Inasistencia administrativa por reloj checador";
                        $oResume->counter = $incidentsGrouped['INA_AD_REL_CH']->count();
                        break;
    
                    case \SCons::INC_TYPE['INA_AD_SUSP']:
                        $oResume->text = "Inasistencia administrativa por suspensión";
                        $oResume->counter = $incidentsGrouped['INA_AD_SUSP']->count();
                        break;
    
                    case \SCons::INC_TYPE['INA_AD_OT']:
                        $oResume->text = "Inasistencia administrativa por otros motivos";
                        $oResume->counter = $incidentsGrouped['INA_AD_OT']->count();
                        break;
    
                    case \SCons::INC_TYPE['ONOM_EXT']:
                        $oResume->text = "Onomástico";
                        $oResume->counter = $incidentsGrouped['ONOM_EXT']->count();
                        break;
    
                    case \SCons::INC_TYPE['RIESGO']:
                        $oResume->text = "Riesgo de trabajo";
                        $oResume->counter = $incidentsGrouped['RIESGO']->count();
                        break;
    
                    case \SCons::INC_TYPE['ENFERMEDAD']:
                        $oResume->text = "Enfermedad en general (Incapacidad)";
                        $oResume->counter = self::calculateDays($incidentsGrouped['ENFERMEDAD']);
                        break;
    
                    case \SCons::INC_TYPE['MATER']:
                        $oResume->text = "Maternidad";
                        $oResume->counter = self::calculateDays($incidentsGrouped['MATER']);
                        break;
    
                    case \SCons::INC_TYPE['LIC_CUIDADOS']:
                        $oResume->text = "Licencia por cuidados médicos de hijos diagnosticados con cáncer";
                        $oResume->counter = self::calculateDays($incidentsGrouped['LIC_CUIDADOS']);
                        break;
    
                    case \SCons::INC_TYPE['VAC']:
                        $oResume->text = "Vacaciones";
                        $oResume->counter = self::calculateDaysForVacation($incidentsGrouped['VAC'])[0];
                        $oResume->lDays = self::calculateDaysForVacation($incidentsGrouped['VAC'])[1];
                        break;
    
                    case \SCons::INC_TYPE['CAPACIT']:
                        $oResume->text = "Capacitación";
                        $oResume->counter = $incidentsGrouped['CAPACIT']->count();
                        break;
    
                    case \SCons::INC_TYPE['TRAB_F_PL']:
                        $oResume->text = "Trabajo fuera de planta";
                        $oResume->counter = $incidentsGrouped['TRAB_F_PL']->count();
                        break;
    
                    case \SCons::INC_TYPE['PATER']:
                        $oResume->text = "Paternidad";
                        $oResume->counter = self::calculateDays($incidentsGrouped['PATER']);
                        break;
    
                    case \SCons::INC_TYPE['DIA_OTOR']:
                        $oResume->text = "Día otorgado";
                        $oResume->counter = $incidentsGrouped['DIA_OTOR']->count();
                        break;
    
                    case \SCons::INC_TYPE['INA_PRES_MED']:
                        $oResume->text = "Inasistencia prescripción médica";
                        $oResume->counter = $incidentsGrouped['INA_PRES_MED']->count();
                        break;
    
                    case \SCons::INC_TYPE['DESCANSO']:
                        $oResume->text = "Descanso";
                        $oResume->counter = $incidentsGrouped['DESCANSO']->count();
                        break;
    
                    case \SCons::INC_TYPE['INA_TR_F_PL']:
                        $oResume->text = "Inasistencia trabajo fuera de planta";
                        $oResume->counter = $incidentsGrouped['INA_TR_F_PL']->count();
                        break;
    
                    case \SCons::INC_TYPE['VAC_CAP']:
                        $oResume->text = "Vacaciones";
                        $oResume->counter = self::calculateDaysForVacation($incidentsGrouped['VAC_CAP'])[0];
                        $oResume->lDays = self::calculateDaysForVacation($incidentsGrouped['VAC_CAP'])[1];
                        break;
    
                    case \SCons::INC_TYPE['INC_CAP']:
                        $oResume->text = "Incapacidad";
                        $oResume->counter = self::calculateDays($incidentsGrouped['INC_CAP']);
                        break;
    
                    case \SCons::INC_TYPE['ONOM_CAP']:
                        $oResume->text = "Onomástico";
                        $oResume->counter = $incidentsGrouped['ONOM_CAP']->count();
                        break;
    
                    case \SCons::INC_TYPE['PERM']:
                        $oResume->text = "Permiso";
                        $oResume->counter = $incidentsGrouped['PERM']->count();
                        break;
    
                    case \SCons::INC_TYPE['DAY_HOLIDAY']:
                        $oResume->text = "Día feriado";
                        $oResume->counter = $incidentsGrouped['DAY_HOLIDAY']->count();
                        break;
    
                    case \SCons::INC_TYPE['PERM_BY_GONE']:
                        $oResume->text = "Permiso por ausencia";
                        $oResume->counter = $incidentsGrouped['PERM_BY_GONE']->count();
                        break;
    
                    case \SCons::INC_TYPE['ELECTION_DAY_2024']:
                        $oResume->text = "Permiso por elección 2024";
                        $oResume->counter = $incidentsGrouped['ELECTION_DAY_2024']->count();
                        break;
    
                    default:
                        Log::warning("Tipo de incidencia no encontrado: " . $incidentType);
                }
    
                // Agregar el resumen a la lista de incidencias
                if ($oResume->counter > 0) {
                    $oEmpData->aIncidents[] = $oResume;
                }
            }
        }

        return $lData;
    }
    
    private static function calculateDays($incidentRecords) {
        $days = 0;
        foreach ($incidentRecords as $record) {
            $startDate = Carbon::parse($record->start_date)->locale('es');
            $endDate = Carbon::parse($record->end_date)->locale('es');
            $days += $startDate->diffInDays($endDate) + 1;
        }
        return $days;
    }
    
    private static function calculateDaysForVacation($incidentRecords) {
        $days = 0;
        $lDays = [];
        foreach ($incidentRecords as $record) {
            $incidentDays = incidentDay::where('incidents_id', $record->id)->get();
            $days += count($incidentDays);
            $lDays = array_merge($lDays, $incidentDays->toArray());
        }
        return [$days, $lDays];
    }
    
}