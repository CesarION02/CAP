<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdjustToSpecialworkshift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specialworkshift', function (Blueprint $table) {
            $table->bigInteger('adjust_id')->unsigned()->nullable()->default(null);

            $table->foreign('adjust_id')->references('id')->on('prepayroll_adjusts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('specialworkshift', function (Blueprint $table) {
            $table->dropColumn('adjust_id');
        });
    }
}
