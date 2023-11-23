<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpVsPayrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emp_vs_payroll', function (Blueprint $table) {
            $table->increments('id_empvspayroll');
            $table->integer('emp_id')->unsigned();
            $table->integer('num_week')->nullable()->unsigned();
            $table->integer('num_biweek')->nullable()->unsigned();
            $table->boolean('have_bonus')->default(false);
            $table->integer('time_delay_real')->default(0);
            $table->integer('time_delay_justified')->default(0);
            $table->integer('time_delay_permission')->default(0);
            $table->integer('early_departure_original')->default(0);
            $table->integer('early_departure_permission')->default(0);
            $table->integer('te_stps')->default(0);
            $table->integer('te_work')->default(0);
            $table->integer('te_adjust')->default(0);
            $table->integer('te_total')->default(0);
            $table->timestamps();

            $table->foreign('emp_id')->references('id')->on('employees');
            $table->foreign('num_week')->references('id')->on('week_cut');
            $table->foreign('num_biweek')->references('id')->on('hrs_prepay_cut');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emp_vs_payroll');
    }
}
