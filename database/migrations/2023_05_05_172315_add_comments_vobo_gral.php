<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommentsVoboGral extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_report_auth_controls', function (Blueprint $table) {
            $table->string('comments', 300)->after('dt_rejected')->default("");
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
            $table->dropColumn('comments');
        });
    }
}
