<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollAdjustsTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_adjusts_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_code');
            $table->string('type_name');
        });

        DB::table('prepayroll_adjusts_types')->insert([
        	['id' => '1', 'type_code' => 'JE', 'type_name' => 'JUSTIFICAR ENTRADA'],
        	['id' => '2', 'type_code' => 'JS', 'type_name' => 'JUSTIFICAR SALIDA'],
        	['id' => '3', 'type_code' => 'OR', 'type_name' => 'OMITIR RETARDO'],
        	['id' => '4', 'type_code' => 'OF', 'type_name' => 'OMITIR FALTA'],
        	['id' => '5', 'type_code' => 'DTE', 'type_name' => 'DESCONTAR TIEMPO EXTRA'],
        	['id' => '6', 'type_code' => 'ATE', 'type_name' => 'AGREGAR TIEMPO EXTRA'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_adjusts_types');
    }
}
