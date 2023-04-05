<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programmed_tasks_logs', function (Blueprint $table) {
            $table->bigIncrements('id_log');
            $table->enum('status', ['iniciada', 'descartada', 'terminada', 'error']);
            $table->text('cur_cfg');
            $table->string('log_message', 500);
            $table->bigInteger('task_id')->unsigned();
            $table->timestamps();

            $table->foreign('task_id')->references('id_task')->on('programmed_tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('programmed_tasks_logs');
    }
}
