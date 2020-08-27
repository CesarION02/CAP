<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHrsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('benefit_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->string('description')->default('');
        });

        DB::table('benefit_policies')->insert([
            ['id' => '1','name' => 'Estricto (siempre debe checar)', 'description' => 'Los que siempre tienen que checar, en caso de no tener checada, es una falta.'],
        	['id' => '2','name' => 'Libre (no es necesario checar)', 'description' => 'Los que da igual si checan o no, se entregaran los bonos.'],
        	['id' => '3','name' => 'Eventual (si checa se cuenta)', 'description' => 'Los que importan las checadas cuando las haya para checar que no tengan retardos.'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('benefit_policies');
    }
}
