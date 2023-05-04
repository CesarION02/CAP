<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncidentExtSysLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incident_ext_sys_links', function (Blueprint $table) {
            $table->increments('id_link');
            $table->integer('incident_id')->unsigned();
            $table->string('external_key');
            $table->enum('external_system', ['pgh', 'siie']);
            $table->timestamps();

            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('restrict')->onUpdate('restrict');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->boolean('is_external')->after('id')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incident_ext_sys_links');

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('is_external');
        });
    }
}
