<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveHeadUserFromPreyarollgroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users_vs_groups = \DB::table('prepayroll_groups')->where('is_delete', false)->pluck('head_user_id', 'id_group');

        Schema::table('prepayroll_groups', function (Blueprint $table) {
            $table->dropForeign(['head_user_id']);

            $table->dropColumn('head_user_id');
            $table->dropColumn('group_code');
        });

        foreach ($users_vs_groups as $id_group => $head_user_id) {
            \DB::table('prepayroll_groups_users')->insert(
                    ['group_id' => $id_group,
                    'head_user_id' => $head_user_id,
                    'user_by_id' => 1]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayroll_groups', function (Blueprint $table) {
            $table->string('group_code', 10)->after('id_group');
            $table->integer('head_user_id')->unsigned()->after('group_name');

            $table->foreign('head_user_id')->references('id')->on('users');
        });
    }
}
