<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkCutSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_template', function (blueprint $table) {
            $table->integer('cut_id')->unsigned()->nullable()->after('overtimepershift');

            $table->foreign('cut_id')->references('id')->on('cut_ed')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workshifts', function($table)
        {
            $table->dropForeign(['cut_id']);

            $table->dropColumn('cut_id');
        });
    }
}
