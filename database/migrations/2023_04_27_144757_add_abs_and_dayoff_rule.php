<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAbsAndDayoffRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar registro(regla) a la tabla de comments_control
        DB::table('comments_control')->insert([
            'id_commentControl' => '48',
            'key_code' => 'hasAbsenceAndDayOffWorked',
            'Comment' => 'Falta y descanso trabajado',
            'value' => '1',
            'is_delete' => '0',
            'created_by' => '1',
            'updated_by' => '1'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar registro(regla) a la tabla de comments_control
        DB::table('comments_control')->where('id_commentControl', '=', '48')
                                    ->delete();
    }
}
