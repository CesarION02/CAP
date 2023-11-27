<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalDatesToEmpVsPayroll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emp_vs_payroll', function (Blueprint $table) {
            $table->date('external_date_end')->after('te_total');
            $table->date('external_date_ini')->after('te_total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emp_vs_payroll', function (Blueprint $table) {
            $table->dropColumn('external_date_end');
            $table->dropColumn('external_date_ini');
        });
    }
}
