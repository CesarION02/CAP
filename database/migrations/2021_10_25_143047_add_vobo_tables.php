<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoboTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_report_configs', function (Blueprint $table) {
            $table->increments('id_configuration');
            $table->date('since_date')->nullable();
            $table->boolean('is_week')->default(false);
            $table->boolean('is_biweek')->default(false);
            $table->boolean('is_required')->default(false);
            $table->integer('order_vobo');
            $table->string('rol_n_name', 50)->nullable();
            $table->integer('user_n_id')->nullable()->unsigned();
            $table->integer('is_delete')->default(0);
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();

            $table->foreign('user_n_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('prepayroll_report_auth_controls', function (Blueprint $table) {
            $table->increments('id_control');
            $table->boolean('is_week')->default(false);
            $table->integer('num_week')->unsigned()->nullable();
            $table->boolean('is_biweek')->default(false);
            $table->integer('num_biweek')->unsigned()->nullable();
            $table->integer('year')->unsigned();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_vobo')->default(false);
            $table->dateTime('dt_vobo')->nullable();
            $table->boolean('is_rejected')->default(false);
            $table->dateTime('dt_rejected')->nullable();
            $table->integer('order_vobo');
            $table->integer('is_delete')->default(0);
            $table->integer('user_vobo_id')->unsigned();
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();

            $table->foreign('num_week')->references('id')->on('week_cut');
            $table->foreign('num_biweek')->references('id')->on('hrs_prepay_cut');
            $table->foreign('user_vobo_id')->references('id')->on('users');
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
        Schema::dropIfExists('prepayroll_report_auth_controls');
        Schema::dropIfExists('prepayroll_report_configs');
    }
}
