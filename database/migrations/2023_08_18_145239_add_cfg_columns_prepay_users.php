<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCfgColumnsPrepayUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         // agregar columnas a la tabla prepayroll_report_configs
         Schema::table('prepayroll_groups_users', function (Blueprint $table) {
            $table->mediumText('cfg_prepayroll')->after('head_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayroll_groups_users', function (Blueprint $table) {
            $table->dropColumn('cfg_prepayroll');
        });
    }
}
