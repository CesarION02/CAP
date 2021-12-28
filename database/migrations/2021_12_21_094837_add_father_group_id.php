<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFatherGroupId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_groups', function (Blueprint $table) {
            $table->integer('father_group_n_id')->after('head_user_id')->unsigned()->default(null)->nullable();

            $table->foreign('father_group_n_id', 'f_g_foreign')->references('id_group')->on('prepayroll_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayroll_groups', function (Blueprint $table) {
            $table->dropForeign(['father_group_n_id']);
            $table->dropColumn('father_group_n_id');
        });
    }
}
