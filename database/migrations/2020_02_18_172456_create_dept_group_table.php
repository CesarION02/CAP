<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeptGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_group', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('is_delete')->default(0);
        });

        DB::table('department_group')->insert([
        	['id' => '1','name' => 'Planta Turnos','is_delete' => '0'],
            ['id' => '2','name' => 'Planta Fijos','is_delete' => '0'],
            ['id' => '3','name' => 'Administración','is_delete' => '0'],
        ]);

       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('department_group');
    }
}
