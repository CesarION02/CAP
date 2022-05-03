<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollReportDelegationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_report_delegations', function (Blueprint $table) {
            $table->increments('id_delegation');
            $table->integer('number_prepayroll')->unsigned();
            $table->integer('year')->unsigned();
            $table->text('json_insertions');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete');
            $table->integer('pay_way_id')->unsigned();
            $table->integer('user_delegation_id')->unsigned();
            $table->integer('user_delegated_id')->unsigned();
            $table->integer('user_insert_id')->unsigned();
            $table->integer('user_update_id')->unsigned();
            $table->timestamps();

            $table->foreign('pay_way_id')->references('id')->on('way_pay');
            $table->foreign('user_delegation_id')->references('id')->on('users');
            $table->foreign('user_delegated_id')->references('id')->on('users');
            $table->foreign('user_insert_id')->references('id')->on('users');
            $table->foreign('user_update_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_report_delegations');
    }
}
