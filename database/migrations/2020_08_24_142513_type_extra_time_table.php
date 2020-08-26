<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TypeExtraTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_extratime', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('is_delete')->default(0);
        });

        DB::table('policy_extratime')->insert([
        	['id' => '1','name' => 'Nunca genera','is_delete' => '0'],
            ['id' => '2','name' => 'Siempre genera','is_delete' => '0'],
            ['id' => '3','name' => 'En ocaciones genera','is_delete' => '0'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('policy_extratime');
    }
}
