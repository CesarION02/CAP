<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('is_delete')->default(0);
        });

        DB::table('areas')->insert([
        	['id' => '1','name' => 'Oficinas','is_delete' => '0'],
            ['id' => '2','name' => 'Oficinas Planta','is_delete' => '0'],
            ['id' => '3','name' => 'Planta','is_delete' => '0'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
    }
}
