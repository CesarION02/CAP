<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollAuthControls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_auth_controls', function (Blueprint $table) {
            $table->bigIncrements('id_control');
            $table->bigInteger('prepayroll_adjust_id')->unsigned();
            $table->integer('user_auth_id')->unsigned();
            $table->boolean('is_authorized')->default(false);
            $table->dateTime('dt_authorization')->nullable();
            $table->boolean('is_rejected')->default(false);
            $table->integer('rejected_by')->unsigned()->nullable();
            $table->integer('is_delete')->default(0);
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();

            $table->foreign('prepayroll_adjust_id')->references('id')->on('prepayroll_adjusts');
            $table->foreign('user_auth_id')->references('id')->on('users');
            $table->foreign('rejected_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_auth_controls');
    }
}
