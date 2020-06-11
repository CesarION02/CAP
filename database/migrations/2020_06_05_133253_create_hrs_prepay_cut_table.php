<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHrsPrepayCutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hrs_prepay_cut', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('external_id')->nullable();
            $table->integer('year');
            $table->integer('num');
            $table->date('dt_cut');
            $table->boolean('is_delete');

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
        Schema::dropIfExists('hrs_prepay_cut');
    }
}
