<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdjustLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adjust_link', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('adjust_id')->unsigned()->nullable()->default(null);
            $table->integer('is_special')->nullable()->default(null);
            $table->integer('special_id')->unsigned()->nullable()->default(null);
            $table->integer('is_incident')->nullable()->default(null);
            $table->integer('incident_id')->unsigned()->nullable()->default(null);

            $table->foreign('adjust_id')->references('id')->on('prepayroll_adjusts');
            $table->foreign('special_id')->references('id')->on('specialworkshift');
            $table->foreign('incident_id')->references('id')->on('incidents');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adjust_link');
    }
}
