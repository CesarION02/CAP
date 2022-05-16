<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCfgFkIdVobos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_report_auth_controls', function (Blueprint $table) {
            $table->integer('cfg_id')->after('is_delete')->nullable()->unsigned()->default(null);

            $table->foreign('cfg_id')->references('id_configuration')->on('prepayroll_report_configs')->onDelete('cascade');
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
            $table->dropForeign(['cfg_id']);
            $table->dropColumn('cfg_id');
        });
    }
}
