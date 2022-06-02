<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobRhTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_rh', function (Blueprint $table) {
            $table->increments('id');
            $table->string('job', 100);
            $table->string('acronym', 100);
            $table->integer('num_positions');
            $table->integer('hierarchical_level');
            $table->boolean('is_deleted');
            $table->integer('external_id')->unsigned();
            $table->integer('dept_rh_id')->unsigned();
            $table->timestamps();

            $table->foreign('dept_rh_id')->references('id')->on('dept_rh')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_rh');
    }
}
