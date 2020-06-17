<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsProcessedData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_data', function (blueprint $table) {
            $table->datetime('indatetimesch')->nullable();
            $table->datetime('outdatetimesch')->nullable();
            $table->integer('prematureout')->nullable();
            $table->integer('overminstotal')->nullable();
            $table->integer('dayinhability')->nullable();
            $table->integer('dayvacation')->nullable();
            $table->boolean('haschecks')->nullable();
            $table->boolean('ischecksschedule')->nullable();
            $table->boolean('istypedaychecked')->nullable();
            $table->boolean('hasabsence')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
