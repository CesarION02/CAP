<?php

use App\Http\Controllers\incidentController;
use App\Models\incident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyResaveIncidents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
