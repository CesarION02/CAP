<?php namespace App\SValidations;

use App\Models\incident;
use App\Models\typeincident;
use Carbon\Carbon;
use DB;

class SIncidentValidations
{
    /**
     * Valida que la incidencia no se empalme con otras incidencias o días festivos
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $idEmployee
     * @param int $idIncident
     * 
     * @return array
     */
    public static function validateIncidentsAndHolidays($startDate, $endDate, $idEmployee, $idIncident)
    {
        $incidents = DB::table('incidents')
            ->where('employee_id', '=', $idEmployee)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereIn('incidents.start_date', [$startDate, $endDate])
                    ->orwhereIn('incidents.end_date', [$startDate, $endDate]);
            })
            ->where('is_delete', 0);

        if ($idIncident > 0) {
            $incidents = $incidents->where('id', '!=', $idIncident);
        }

        $incidents = $incidents->get();

        if (count($incidents) > 0) {
            return [
                'code' => 400,
                'status' => 'error',
                'message' => "Ya existe una incidencia en el rango de fechas seleccionado para este empleado.",
            ];
        }

        $holidays = DB::table('holidays')
            ->whereIn('fecha', [$startDate, $endDate])
            ->where('is_delete', 0)
            ->get();

        if (count($holidays) > 0) {
            return [
                'code' => 400,
                'status' => 'error',
                'message' => "Ya existe un día festivo en el rango de fechas seleccionado.",
            ];
        }

        return [
            'code' => 200,
            'status' => 'success',
            'message' => "OK",
        ];
    }

    /**
     * Asigna un subtipo de incidencia por defecto si no se ha seleccionado uno
     * 
     * @param incident $oIncident
     * @param string $notes
     * @param int|null $holidayWorked
     * @return mixed
     */
    public static function manageIncident($oIncident, $notes, $holidayWorked)
    {
        if (! isset($oIncident->id) || $oIncident->id == 0 || is_null($oIncident->id)) {
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
                case \SCons::INC_TYPE['DAY_HOLIDAY']:
                case \SCons::INC_TYPE['PERM_BY_GONE']:
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
        }
        $oIncident->nts = $notes;
        if ($oIncident->type_incidents_id == 17) {
            $oIncident->holiday_worked_id = $holidayWorked;
        }
        else {
            $oIncident->holiday_worked_id = null;
        }

        if (! isset($oIncident->type_sub_inc_id) || is_null($oIncident->type_sub_inc_id) || $oIncident->type_sub_inc_id == 0) {
            $oSubType = typeincident::find($oIncident->type_incidents_id);
            if ($oSubType->has_subtypes) {
                $lSubTypes = DB::table('type_sub_incidents')->where('incident_type_id', $oIncident->type_incidents_id)
                                ->where('is_delete', 0)
                                ->orderBy('updated_at', 'DESC')
                                ->get();

                if (count($lSubTypes) > 0) {
                    $default = 0;
                    foreach ($lSubTypes as $oSubType) {
                        if ($oSubType->is_default) {
                            $default = $oSubType->id_sub_incident;
                            break;
                        }
                    }
                    if ($default == 0) {
                        $default = $lSubTypes[0]->id_sub_incident;
                    }
                    $oIncident->type_sub_inc_id = $default;
                }
                else {
                    $oIncident->type_sub_inc_id = null;
                }
            }
        }

        return $oIncident;
    }
}
