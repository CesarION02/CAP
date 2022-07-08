<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollReportVobosSkiped extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_report_vobos_skipped', function (Blueprint $table) {
            $table->increments('id_skipped');
            $table->boolean('is_week')->default(false);
            $table->integer('num_week')->unsigned()->nullable();
            $table->boolean('is_biweek')->default(false);
            $table->integer('num_biweek')->unsigned()->nullable();
            $table->integer('year')->unsigned();
            $table->datetime('dt_skipped');
            $table->boolean('is_delete');
            $table->integer('skipped_by_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('skipped_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_report_vobos_skipped');
    }
}
