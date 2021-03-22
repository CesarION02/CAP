<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogsAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id')->unsigned();
            $table->dateTime('dt_time_log');
            $table->integer('mins_in');
            $table->integer('mins_out');
            $table->string('source');
            $table->boolean('is_authorized');
            $table->string('message');
            $table->dateTime('sch_in_dt_time')->nullable();
            $table->dateTime('sch_out_dt_time')->nullable();
            $table->integer('is_delete')->default(0);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_logs');
    }
}
