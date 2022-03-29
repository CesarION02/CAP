<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreparollgroupsVsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_groups_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->integer('head_user_id')->unsigned();
            $table->integer('user_by_id')->unsigned();
            $table->timestamps();

            $table->unique(['group_id', 'head_user_id']);

            $table->foreign('group_id')->references('id_group')->on('prepayroll_groups');
            $table->foreign('head_user_id')->references('id')->on('users');
            $table->foreign('user_by_id')->references('id')->on('users');
        });
        
        Schema::table('prepayroll_group_employees', function (Blueprint $table) {
            $table->unique(['group_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preparoll_groups_users');

        Schema::table('prepayroll_group_employees', function (Blueprint $table) {
            $table->dropUnique(['group_id', 'employee_id']);
        });
    }
}
