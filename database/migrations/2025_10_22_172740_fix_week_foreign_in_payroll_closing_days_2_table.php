<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixWeekForeignInPayrollClosingDays2Table extends Migration
{
    public function up()
    {
        Schema::table('payroll_closing_days', function (Blueprint $table) {
            // Primero eliminamos la foreign vieja
            $table->dropForeign(['week_id']);

            // Luego creamos la correcta
            $table->foreign('week_id')->references('id')->on('week_cut');
        });
    }

    public function down()
    {
        Schema::table('payroll_closing_days', function (Blueprint $table) {
            $table->dropForeign(['week_id']);
            $table->foreign('week_id')->references('id')->on('week');
        });
    }
}
