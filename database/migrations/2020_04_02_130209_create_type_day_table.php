<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypeDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_day', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('short_name');
        });

        DB::table('type_day')->insert([
        	['id' => '1','name' => 'NORMAL','short_name' => 'NORMAL'],
            ['id' => '2','name' => 'INCAPACIDAD','short_name' => 'INC'],
            ['id' => '3','name' => 'VACACIONES','short_name' => 'VAC'],
            ['id' => '4','name' => 'DIA FESTIVO','short_name' => 'FEST'],
            ['id' => '5','name' => 'DESCANSO','short_name' => 'DESC'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_day');
    }
}
