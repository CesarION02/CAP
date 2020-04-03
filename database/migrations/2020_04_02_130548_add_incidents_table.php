<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incidents', function (blueprint $table) {
            $table->integer('cls_inc_id')->unsigned()->after('type_incidents_id');
            $table->integer('external_id')->after('id');
            $table->integer('num')->after('external_id');
            $table->integer('eff_day')->after('end_date');
            $table->integer('ben_year')->after('eff_day');
            $table->integer('ben_ann')->after('ben_year');
            $table->string('nts')->nullable()->after('ben_ann');

            $table->foreign('cls_inc_id')->references('id')->on('class_incident')->onDelete('cascade');
        });
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
