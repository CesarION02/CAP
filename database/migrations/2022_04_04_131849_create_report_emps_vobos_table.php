<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportEmpsVobosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_report_emp_vobos', function (Blueprint $table) {
            $table->bigIncrements('id_vobo');
            $table->boolean('is_week')->default(false);
            $table->integer('num_week')->unsigned()->nullable();
            $table->boolean('is_biweek')->default(false);
            $table->integer('num_biweek')->unsigned()->nullable();
            $table->integer('year')->unsigned();
            $table->boolean('is_delete');
            $table->integer('employee_id')->unsigned();
            $table->integer('vobo_by_id')->unsigned();
            $table->datetime('dt_vobo');
            $table->integer('deleted_by_id')->unsigned()->nullable()->default(null);
            $table->datetime('dt_deleted')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('vobo_by_id')->references('id')->on('users');
            $table->foreign('deleted_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_report_emp_vobos');
    }
}
