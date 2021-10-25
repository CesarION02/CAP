<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_groups', function (Blueprint $table) {
            $table->increments('id_group');
            $table->string('group_code', 10);
            $table->string('group_name', 100);
            $table->integer('head_user_id')->unsigned();
            $table->integer('is_delete')->default(0);
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();

            $table->foreign('head_user_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('prepayroll_group_employees', function (Blueprint $table) {
            $table->increments('id_group_employee');
            $table->integer('group_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->integer('is_delete')->default(0);
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();

            $table->foreign('group_id')->references('id_group')->on('prepayroll_groups');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_group_employees');
        Schema::dropIfExists('prepayroll_groups');
    }
}
