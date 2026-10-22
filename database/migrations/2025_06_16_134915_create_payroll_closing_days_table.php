<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrollClosingDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_closing_days', function (Blueprint $table) {
            $table->increments('id_payroll_closing_days');
            $table->boolean('is_week');
            $table->boolean('is_biweek');
            $table->integer('week_id')->unsigned();
            $table->integer('biweek_id')->unsigned();
            $table->boolean('applies');
            $table->integer('days');
            $table->timestamps();

            $table->foreign('week_id')->references('id')->on('week');
            $table->foreign('biweek_id')->references('id')->on('hrs_prepay_cut');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_closing_days');
    }
}
