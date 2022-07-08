<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsGlobalFlagPpreportCfg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_report_configs', function (Blueprint $table) {
            $table->boolean('is_global')->after('user_n_id');
        });

        Schema::table('prepayroll_report_auth_controls', function (Blueprint $table) {
            $table->boolean('is_global')->after('order_vobo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayroll_report_auth_controls', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });

        Schema::table('prepayroll_report_configs', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });
    }
}
