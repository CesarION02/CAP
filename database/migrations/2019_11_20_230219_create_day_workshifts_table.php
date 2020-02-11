<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDayWorkshiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('day_workshifts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('day_id')->unsigned();
            $table->integer('workshift_id')->unsigned();
            $table->timestamps();
            $table->integer('is_delete');

            $table->foreign('day_id')->references('id')->on('week_department_day')->onDelete('cascade');
            $table->foreign('workshift_id')->references('id')->on('workshifts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('day_workshifts');
    }
}
