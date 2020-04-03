<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncidentsDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incidents_day', function (blueprint $table) {
            $table->increments('id');
            $table->integer('incidents_id')->unsigned();
            $table->date('date');
            $table->integer('num_day');
            $table->integer('is_delete');

            $table->foreign('incidents_id')->references('id')->on('incidents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incidents_day');
    }
}
