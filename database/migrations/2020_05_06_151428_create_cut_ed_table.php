<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCutEdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cut_ed', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('is_delete');
            $table->timestamps();

        });

        
        DB::table('cut_ed')->insert([
        	['id' => '1','name' => 'NA','is_delete' => '0'],
            ['id' => '2','name' => 'Entrada','is_delete' => '0'],
            ['id' => '3', 'name' => 'Salida', 'is_delete' => '0'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cut_ed');
    }
}
