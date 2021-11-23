<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollAuth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_auth_configs', function (Blueprint $table) {
            $table->bigIncrements('id_configuration');
            $table->integer('group_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('is_delete')->default(0);
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();

            $table->foreign('group_id')->references('id_group')->on('prepayroll_groups');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('prepayroll_auth_configs');
        Schema::dropIfExists('prepayroll_auth_levels');
    }
}
