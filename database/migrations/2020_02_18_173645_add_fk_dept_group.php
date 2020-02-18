<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkDeptGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('departments', function (blueprint $table) {
            $table->integer('dept_group_id')->default(1)->after('area_id')->unsigned();

            $table->foreign('dept_group_id')->references('id')->on('department_group')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function($table)
        {
            $table->dropForeign(['dept_group_id']);

            $table->dropColumn('dept_group_id');
        });
    }
}
