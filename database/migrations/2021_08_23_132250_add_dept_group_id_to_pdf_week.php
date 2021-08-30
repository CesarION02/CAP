<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeptGroupIdToPdfWeek extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pdf_week', function (Blueprint $table) {
            $table->integer('dept_group_id')->unsigned()->nullable();

            $table->foreign('dept_group_id')->references('id')->on('department_group');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pdf_week', function (Blueprint $table) {
            $table->dropForeign('dept_group_id');
            $table->dropColumn('dept_group_id');
        });
    }
}
