<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TypeProgrammingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_programming', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('is_delete')->default(0);
        });
    

        DB::table('type_programming')->insert([
            ['id' => '1','name' => 'Rotación','is_delete' => '0'],
            ['id' => '2','name' => 'Rotación solo sabado','is_delete' => '0'],
            ['id' => '3','name' => 'Mixto','is_delete' => '0'],
            ['id' => '4','name' => 'Fijos','is_delete' => '0'], 
        ]);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
