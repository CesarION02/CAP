<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBySystemToGroupDeptUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_dept_user', function (Blueprint $table) {
            $table->boolean('is_system')->default(0)->after('is_delete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_dept_user', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
}
