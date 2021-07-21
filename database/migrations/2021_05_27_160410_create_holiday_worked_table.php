<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidayWorkedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('holiday_worked', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id')->unsigned();
            $table->integer('holiday_id')->unsigned();
            $table->integer('number_assignments');
            $table->integer('is_delete')->default(0);
            
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('holiday_id')->references('id')->on('holidays');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holiday_worked');
    }
}
