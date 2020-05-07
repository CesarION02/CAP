<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkCutWorkshifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workshifts', function (blueprint $table) {
            $table->integer('cut_id')->unsigned()->nullable()->after('order_view');

            $table->foreign('cut_id')->references('id')->on('cut_ed')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workshifts', function($table)
        {
            $table->dropForeign(['cut_id']);

            $table->dropColumn('cut_id');
        });
    }
}
