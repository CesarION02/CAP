<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagReportPpConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_report_configs', function (Blueprint $table) {
            $table->boolean('is_report')->default('1')->after('is_delete');
        });

        DB::table('prepayroll_report_configs')->where('is_delete', 1)->update(['is_report' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayroll_report_configs', function (Blueprint $table) {
            $table->dropColumn('is_report');
        });
    }
}
