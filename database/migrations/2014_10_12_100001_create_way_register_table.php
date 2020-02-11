<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWayRegisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('way_register',function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        DB::table('way_register')->insert([
        	['id' => '1','name' => 'Pendiente'],
        	['id' => '2','name' => 'Huella'],
            ['id' => '3','name' => 'Codigo'],
            ['id' => '4','name' => 'Ambos'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('way_register');
    }
}
