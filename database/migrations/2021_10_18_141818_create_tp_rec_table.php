<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTpRecTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tp_rec', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('in_reports');
            $table->timestamps();
        });

        DB::table('tp_rec')->insert([
        	['id' => '2','name' => 'Sueldos','in_reports' => '1'],
        	['id' => '3','name' => 'Jubilados','in_reports' => '1'],
            ['id' => '4','name' => 'Pensionados','in_reports' => '1'],
            ['id' => '5','name' => 'Asimilados Miembros Sociedades Cooperativas Produccion','in_reports' => '0'],
            ['id' => '6','name' => 'Asimilados Integrantes','in_reports' => '0'],
            ['id' => '7','name' => 'Asimilados Miembros consejos','in_reports' => '0'],
            ['id' => '8','name' => 'Asimilados comisionistas','in_reports' => '0'],
            ['id' => '9','name' => 'Asimilados Honorarios','in_reports' => '0'],
            ['id' => '10','name' => 'Asimilados acciones','in_reports' => '0'],
            ['id' => '11','name' => 'Asimilados otros','in_reports' => '0'],
            ['id' => '12','name' => 'Jubilados o Pensionados','in_reports' => '0'],
            ['id' => '13','name' => 'Indemnización o Separación','in_reports' => '0'],
            ['id' => '99','name' => 'Otro Regimen','in_reports' => '0'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tp_rec');
    }
}
