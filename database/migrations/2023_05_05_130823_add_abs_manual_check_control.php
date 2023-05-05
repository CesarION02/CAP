<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAbsManualCheckControl extends Migration
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
            'id_commentControl' => '49',
            'key_code' => 'hasCheckManual',
            'Comment' => 'Checadas modificadas manualmente',
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
        DB::table('comments_control')->whereIn('id_commentControl', ['49'])
                                    ->delete();
    }
}