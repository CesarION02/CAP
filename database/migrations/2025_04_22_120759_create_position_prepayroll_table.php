<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePositionPrepayrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('position_prepayroll', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_rh_id')->unsigned();
            $table->integer('grp_prepayroll_id')->unsigned();
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();

            $table->foreign('job_rh_id')->references('id')->on('job_rh');
            $table->foreign('grp_prepayroll_id')->references('id_group')->on('prepayroll_groups');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            
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
        Schema::dropIfExists('position_prepayroll');
    }
}
