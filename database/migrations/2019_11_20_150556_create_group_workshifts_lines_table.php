<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupWorkshiftsLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_workshifts_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_workshifts_id')->unsigned();
            $table->integer('workshifts_id')->unsigned();
            $table->timestamps();

            $table->foreign('group_workshifts_id')->references('id')->on('group_workshifts')->onDelete('cascade');
            $table->foreign('workshifts_id')->references('id')->on('workshifts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_workshifts_lines');
    }
}
