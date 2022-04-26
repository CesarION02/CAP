<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRolDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('rol')->insert([
        	['id' => '15',
            'name' => 'DEFAULT',
            'created_at' => now(), 
            'updated_at' => now()],
        ]);

        DB::table('menu')->insert([
        	['id' => '95',
            'menu_id' => '5',
            'name' => 'Reporte tiempo extra delegados',
            'url' => 'report/reportetiemposextradelegados',
            'order' => '5',
            'icono' => null,
            'created_at' => now(), 
            'updated_at' => now()],
        ]);

        DB::table('menu_rol')->insert([
        	['rol_id' => '15', 'menu_id' => '92'],
        	['rol_id' => '15', 'menu_id' => '94'],
        	['rol_id' => '15', 'menu_id' => '95']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menu_rol')->where('menu_id', '95')->where('rol_id', 15)->delete();
        DB::table('user_rol')->where('rol_id', 15)->delete();
        DB::table('rol')->where('id', '15')->delete();
        DB::table('menu')->where('id', '95')->delete();

    }
}
