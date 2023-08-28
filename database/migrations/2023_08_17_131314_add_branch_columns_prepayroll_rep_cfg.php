<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBranchColumnsPrepayrollRepCfg extends Migration
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
            $table->integer('branch')->default(0)->after('order_vobo')->unsigned();
        });
        Schema::table('prepayroll_report_auth_controls', function (Blueprint $table) {
            $table->integer('branch')->default(0)->after('order_vobo')->unsigned();
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
            $table->dropColumn('branch');
        });
        Schema::table('prepayroll_report_auth_controls', function (Blueprint $table) {
            $table->dropColumn('branch');
        });
    }
}
