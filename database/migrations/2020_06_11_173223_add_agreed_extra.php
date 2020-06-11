<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgreedExtra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_template', function (blueprint $table) {
            $table->float('agreed_extra')->nullable()->after('overtimepershift');
        });

        Schema::table('workshifts', function (blueprint $table) {
            $table->float('agreed_extra')->nullable()->after('overtimepershift');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_template', function($table)
        {
            $table->dropColumn('agreed_extra');
        });
        Schema::table('workshifts', function($table)
        {
            $table->dropColumn('agreed_extra');
        });
    }
}
