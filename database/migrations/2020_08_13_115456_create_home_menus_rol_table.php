<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMenusRolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_menus_rol', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rol_id')->unsigned();
            $table->integer('menu_id')->unsigned();
            $table->integer('order')->nullable();
            
            $table->foreign('rol_id')->references('id')->on('rol')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('menu')->onDelete('cascade'); 
        });

        DB::table('home_menus_rol')->insert([
            ['id' => '1', 'rol_id' => '1','menu_id' => '33','order' => '1', 'icono' => 'fa-user-circle-o'],
            ['id' => '2', 'rol_id' => '1','menu_id' => '57','order' => '2', 'icono' => 'fa-hand-pointer-o'],
            ['id' => '3', 'rol_id' => '1','menu_id' => '23','order' => '3', 'icono' => 'fa-file-text-o'],
            ['id' => '4', 'rol_id' => '1','menu_id' => '28','order' => '4', 'icono' => 'fa-calendar-plus-o'],
            ['id' => '5', 'rol_id' => '1','menu_id' => '38','order' => '5', 'icono' => 'fa-calendar-plus-o'],
            ['id' => '6', 'rol_id' => '1','menu_id' => '72','order' => '6', 'icono' => 'fa-user-circle-o'],
            ['id' => '7', 'rol_id' => '7','menu_id' => '57','order' => '1', 'icono' => 'fa-hand-pointer'],
            ['id' => '8', 'rol_id' => '2','menu_id' => '16','order' => '1', 'icono' => 'fa-list-ul'],
            ['id' => '9', 'rol_id' => '2','menu_id' => '39','order' => '2', 'icono' => 'fa-user-circle-o'],
            ['id' => '10', 'rol_id' => '2','menu_id' => '62','order' => '3', 'icono' => 'fa-check-square'],
            ['id' => '11', 'rol_id' => '2','menu_id' => '63','order' => '4', 'icono' => 'fa-calendar-plus-o'],
            ['id' => '12', 'rol_id' => '2','menu_id' => '65','order' => '5', 'icono' => 'fa-file-text-o'],
            ['id' => '13', 'rol_id' => '3','menu_id' => '23','order' => '1', 'icono' => 'fa-file-text-o'],
            ['id' => '14', 'rol_id' => '3','menu_id' => '27','order' => '2', 'icono' => 'fa-calendar'],
            ['id' => '15', 'rol_id' => '3','menu_id' => '33','order' => '3', 'icono' => 'fa-user-circle-o'],
            ['id' => '16', 'rol_id' => '3','menu_id' => '60','order' => '4', 'icono' => 'fa-users'],
            ['id' => '17', 'rol_id' => '3','menu_id' => '70','order' => '5', 'icono' => 'fa-calendar-check-o'],
            ['id' => '18', 'rol_id' => '9','menu_id' => '17','order' => '1', 'icono' => 'fa fa-list-ul'],
            ['id' => '19', 'rol_id' => '9','menu_id' => '39','order' => '2', 'icono' => 'fa-user-circle-o'],
            ['id' => '20', 'rol_id' => '9','menu_id' => '63','order' => '3', 'icono' => 'fa-calendar-plus-o'],
            ['id' => '21', 'rol_id' => '9','menu_id' => '65','order' => '4', 'icono' => 'fa-file-text-o'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('home_menus_rol');
    }
}
