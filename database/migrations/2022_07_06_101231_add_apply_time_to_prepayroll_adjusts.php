<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplyTimeToPrepayrollAdjusts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_adjusts', function (Blueprint $table) {
            $table->boolean('apply_time')->default(true)->after('adjust_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayroll_adjusts', function (Blueprint $table) {
            $table->dropColumn('apply_time');
        });
    }
}
