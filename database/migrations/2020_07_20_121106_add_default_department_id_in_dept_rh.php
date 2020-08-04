<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultDepartmentIdInDeptRh extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dept_rh', function (Blueprint $table) {
            $table->integer('default_dept_id')->unsigned()->nullable();

            $table->foreign('default_dept_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dept_rh', function (Blueprint $table) {
            $table->dropForeign(['default_dept_id']);
            $table->dropColumn('default_dept_id');
        });
    }
}
