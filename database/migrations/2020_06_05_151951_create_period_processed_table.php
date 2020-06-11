<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodProcessedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_processed', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('num_week')->unsigned()->nullable();
            $table->integer('num_biweekly')->unsigned()->nullable();
            $table->boolean('is_week');
            $table->boolean('is_biweekly');
            $table->date('ini_date');
            $table->date('fin_date');
            $table->boolean('is_close');
            $table->timestamps();

            $table->foreign('num_week')->references('id')->on('week_cut');
            $table->foreign('num_biweekly')->references('id')->on('hrs_prepay_cut');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('period_processed');
    }
}
