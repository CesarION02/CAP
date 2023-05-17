<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegLinkAdj extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // agregar columnas a la tabla adjust_link
        Schema::table('adjust_link', function (Blueprint $table) {
            $table->integer('register_id')->unsigned()->nullable()->after('incident_id')->default(null);

            $table->foreign('register_id')->references('id')->on('registers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // eliminar columnas de la tabla adjust_link
        Schema::table('adjust_link', function (Blueprint $table) {
            $table->dropForeign(['register_id']);
            $table->dropColumn('register_id');
        });
    }
}
