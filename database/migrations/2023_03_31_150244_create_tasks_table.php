<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programmed_tasks', function (Blueprint $table) {
            $table->bigIncrements('id_task');
            $table->date('execute_on');
            $table->string('execute_at', 8)->default("00:00:00");
            $table->boolean('apply_time')->default(false);
            $table->dateTime('done_at')->default(null)->nullable();
            $table->text('cfg');
            $table->boolean('is_done')->default(false);
            $table->boolean('is_delete')->default(0);
            $table->integer('task_type_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('programmed_tasks');
    }
}
