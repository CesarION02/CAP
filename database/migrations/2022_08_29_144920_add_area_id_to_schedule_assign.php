<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaIdToScheduleAssign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_assign', function (Blueprint $table) {
            $table->bigInteger('area_id')->unsignned()->nullable()->default(null)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_assign', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
}
