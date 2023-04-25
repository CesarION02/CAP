<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncidentsPayableFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('type_incidents', function (Blueprint $table) {
            $table->boolean('is_payable')->after('is_cap_edit')->default(false);
        });

        DB::table('type_incidents')
                ->whereIn('id', [
                    // INASIST. S/PERMISO	1,
                    // INASIST. C/PERMISO S/GOCE	2,
                    // INASIST. C/PERMISO C/GOCE	
                    3,
                    // INASIST. ADMTIVA. RELOJ CHECADOR	4,
                    // INASIST. ADMTIVA. SUSPENSIÓN	5,
                    // INASIST. ADMTIVA. OTROS	6,
                    // ONOMÁSTICO	
                    7,
                    // Riesgo de trabajo	
                    8,
                    // Enfermedad en general	9,
                    // Maternidad	10,
                    // Licencia por cuidados médicos de hijos diagnosticados con cáncer.	11,
                    // VACACIONES	
                    12,
                    // VACACIONES PENDIENTES	
                    13,
                    // CAPACITACIÓN	
                    14,
                    // TRABAJO FUERA PLANTA	
                    15,
                    // PATERNIDAD	16,
                    // DIA OTORGADO	
                    17,
                    // INASIST. PRESCRIPCION MEDICA	
                    18,
                    // DESCANSO	
                    19,
                    // INASIST. TRABAJO FUERA DE PLANTA	
                    20,
                    // VACACIONES	
                    21,
                    // INCAPACIDAD	22,
                    // ONOMÁSTICO	
                    23,
                    // PERMISO	
                    24
                ])
                ->update([
                    'is_payable' => '1',
                ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_incidents', function (Blueprint $table) {
            $table->dropColumn('is_payable');
        });
    }
}
