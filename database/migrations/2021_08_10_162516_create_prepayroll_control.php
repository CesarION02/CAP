<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollControl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_control', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('period_processed_id')->unsigned()->nullable();;
            $table->integer('status');
            $table->integer('num_week')->unsigned()->nullable();
            $table->integer('num_biweekly')->unsigned()->nullable();
            $table->boolean('is_week');
            $table->boolean('is_biweekly');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();

            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('num_week')->references('id')->on('week_cut');
            $table->foreign('num_biweekly')->references('id')->on('hrs_prepay_cut');
            $table->foreign('period_processed_id')->references('id')->on('period_processed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_control');
    }
}
