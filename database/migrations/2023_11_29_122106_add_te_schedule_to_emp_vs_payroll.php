<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeScheduleToEmpVsPayroll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emp_vs_payroll', function (Blueprint $table) {
            $table->integer('te_schedule')->after('te_stps');
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
            $table->dropColumn('te_schedule');
        });
    }
}
