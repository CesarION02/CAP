<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFksAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_assign', function (blueprint $table) {
            $table->integer('group_schedules_id')->default(null)->after('end_date')->unsigned()->nullable();
            $table->integer('order_gs')->default(null)->after('group_schedules_id')->nullable();

            $table->foreign('group_schedules_id')->references('id')->on('group_schedule')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_assign', function($table)
        {
            $table->dropForeign(['group_schedules_id']);

            $table->dropColumn('group_schedules_id');
            $table->dropColumn('order_gs');
        });
    }
}
