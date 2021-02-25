<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkshiftIdToSpecialworkshift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specialworkshift', function (Blueprint $table) {
            $table->integer('workshift_id')->unsigned();

            $table->foreign('workshift_id')->references('id')->on('workshifts')->onDelete('cascade');
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
            $table->dropColumn('workshift_id');
        });
    }
}
