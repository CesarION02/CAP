<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeDayDayWorkshiftsEmployeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('day_workshifts_employee', function (blueprint $table) {
            $table->integer('type_day_id')->unsigned()->default(1)->after('job_id');
            $table->foreign('type_day_id')->references('id')->on('type_day')->onDelete('cascade');
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
