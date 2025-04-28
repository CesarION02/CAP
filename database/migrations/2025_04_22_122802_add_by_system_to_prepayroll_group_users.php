<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBySystemToPrepayrollGroupUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_groups_users', function (Blueprint $table) {
            $table->boolean('is_system')->default(0)->after('user_by_id');    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayroll_group_users', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
}
