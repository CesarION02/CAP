<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeekDepartmentDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('week_department_day', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->integer('week_department_id')->unsigned();
            $table->integer('status')->unsigned();

            $table->foreign('week_department_id')->references('id')->on('week_department')->onDelete('cascade');
            $table->foreign('status')->references('id')->on('status_department')->onDelete('cascade');
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
        Schema::dropIfExists('week_department_day');
    }
}
