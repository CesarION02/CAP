<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollgroupsVsDeptos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_group_deptos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->integer('department_id')->unsigned();
            $table->integer('user_by_id')->unsigned();
            $table->timestamps();

            $table->unique(['group_id', 'department_id']);

            $table->foreign('group_id')->references('id_group')->on('prepayroll_groups');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('user_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_group_deptos');
    }
}
