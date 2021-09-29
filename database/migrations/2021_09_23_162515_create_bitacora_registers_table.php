<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitacoraRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitacora_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->boolean('type')->nullable();
            $table->integer('usuario_id')->unsigned();
            $table->integer('register_id')->unsigned();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users');
            $table->foreign('register_id')->references('id')->on('registers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bitacora_registers');
    }
}
