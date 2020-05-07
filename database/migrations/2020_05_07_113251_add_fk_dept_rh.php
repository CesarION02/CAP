<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkDeptRh extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (blueprint $table) {
            $table->integer('dept_rh_id')->unsigned()->nullable()->after('external_id');

            $table->foreign('dept_rh_id')->references('id')->on('dept_rh')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function($table)
        {
            $table->dropForeign(['dept_rh_id']);

            $table->dropColumn('dept_rh_id');
        });
    }
}
