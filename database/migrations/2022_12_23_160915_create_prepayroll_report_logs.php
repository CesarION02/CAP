<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollReportLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_report_logs', function (Blueprint $table) {
            $table->bigIncrements('id_log');
            $table->string('id_generation', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('programmed_schedule_n', 150)->nullable();
            $table->string('detected_schedule_n', 150)->nullable();
            $table->enum('adjust_by_system', ['checada_omitida', 'checada_cambio', 'cambio_horario']);
            $table->integer('register_n_id')->unsigned()->nullable();
            $table->integer('type_reg_orig_n_id')->unsigned()->nullable();
            $table->integer('way_pay_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->integer('user_by_id')->unsigned();
            $table->timestamps();

            $table->foreign('register_n_id')->references('id')->on('registers');
            $table->foreign('type_reg_orig_n_id')->references('id')->on('type_registers');
            $table->foreign('way_pay_id')->references('id')->on('way_pay');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('user_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_report_logs');
    }
}
