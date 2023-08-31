<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupColumnPrepayConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // agregar columnas a la tabla prepayroll_report_configs
        Schema::table('prepayroll_report_configs', function (Blueprint $table) {
            $table->integer('group_n_id')->after('user_n_id')->nullable()->unsigned();

            $table->foreign('group_n_id')->references('id_group')->on('prepayroll_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // eliminar columnas de la tabla prepayroll_report_configs
        Schema::table('prepayroll_report_configs', function (Blueprint $table) {
            $table->dropForeign(['group_n_id']);
            $table->dropColumn('group_n_id');
        });
    }
}
