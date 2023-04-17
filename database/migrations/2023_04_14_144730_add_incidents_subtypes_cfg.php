<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncidentsSubtypesCfg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('type_incidents', function (Blueprint $table) {
            $table->string('ext_pgh_id', 15)->after('name')->default(null)->nullable();
            $table->string('ext_siie_id', 15)->after('ext_pgh_id')->default(null)->nullable();
            $table->boolean('has_subtypes')->after('is_payable')->default(false);
        });

        Schema::create('type_sub_incidents', function (Blueprint $table) {
            $table->increments('id_sub_incident');
            $table->string('name', 150);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->integer('incident_type_id')->unsigned();
            $table->string('ext_pgh_id', 15)->default(null)->nullable();
            $table->string('ext_siie_id', 15)->default(null)->nullable();
            $table->timestamps();

            $table->foreign('incident_type_id')->references('id')->on('type_incidents');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->integer('type_sub_inc_id')->after('cls_inc_id')->default(null)->nullable()->unsigned();

            $table->foreign('type_sub_inc_id')->references('id_sub_incident')->on('type_sub_incidents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_incidents', function (Blueprint $table) {
            $table->dropColumn('ext_pgh_id');
            $table->dropColumn('ext_siie_id');
            $table->dropColumn('has_subtypes');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropForeign(['type_sub_inc_id']);
            $table->dropColumn('type_sub_inc_id');
        });

        Schema::dropIfExists('type_sub_incidents');
    }
}
