<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyExtKeyIncidents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incidents', function($table)
        {
            $table->string('external_key')->default('')->after('id');
            $table->dropColumn('external_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incidents', function($table)
        {
            $table->integer('external_id')->unsigned()->after('id');
            $table->dropColumn('external_key');
        });
    }
}
