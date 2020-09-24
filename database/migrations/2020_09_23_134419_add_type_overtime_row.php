<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeOvertimeRow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workshifts', function (blueprint $table) {
            $table->integer('overtime_check_policy')->unsigned()->after('order_view')->default(2);
        });

        Schema::table('schedule_template', function (blueprint $table) {
            $table->integer('overtime_check_policy')->unsigned()->after('agreed_extra')->default(2);
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
            $table->dropColumn('overtime_check_policy');
        });

        Schema::table('schedule_template', function($table)
        {
            $table->dropColumn('overtime_check_policy');
        });
    }
}
