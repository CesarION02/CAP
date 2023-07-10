<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCapLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cap_locks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('got_at')->nullable();
            $table->dateTime('released_at')->nullable();
            // tiempo en segundos
            $table->integer('timer')->default(300);
            $table->string('completion_code', 5)->default('');
            $table->enum('lock_type', ['sincronizacion', 'registro'])->default('sincronizacion');
            $table->integer('user_id')->unsigned()->nullable();
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cap_locks');
    }
}
