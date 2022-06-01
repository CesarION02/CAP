<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsControlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments_control', function (Blueprint $table) {
            $table->bigIncrements('id_commentControl');
            $table->string('key_code');
            $table->string('Comment')->nullable();
            $table->boolean('value');
            $table->boolean('created_by');
            $table->boolean('updated_by');
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });

        $values = [
            ['key_code' => 'prematureOut', 'Comment' => null, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'delayMins', 'Comment' => null, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'overWorkedMins', 'Comment' => 'Tiempo extra', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'hasAbsence', 'Comment' => 'Falta por omitir checar', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isDayOff', 'Comment' => 'No laborable', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isHoliday', 'Comment' => null, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'dayInhability', 'Comment' => null, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'dayVacations', 'Comment' => null, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'hasAssign', 'Comment' => null, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'hasChecks', 'Comment' => 'Sin checadas', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'hasSchedule', 'Comment' => 'Sin horario', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isSpecialSchedule', 'Comment' => null, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'hasCheckOut', 'Comment' => 'Sin salida', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'hasCheckIn', 'Comment' => 'Sin entrada', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isAtypicalIn', 'Comment' => 'Entrada atípica', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isAtypicalOut', 'Comment' => 'Salida atípica', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isCheckSchedule', 'Comment' => 'Revisar horario', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'workable', 'Comment' => 'No laborable', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'entryDelayMinutes', 'Comment' => 'Retardo', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isIncompleteTeJourney', 'Comment' => 'Jornada TE', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isDayRepeated', 'Comment' => 'Dia repetido', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key_code' => 'isSpecialSchedule', 'Comment' => 'Turno especial', 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()]
        ];

        $incidentsType = \DB::table('type_incidents')->get();

        foreach($incidentsType as $inc){
            array_push($values, ['key_code' => $inc->id, 'Comment' => $inc->name, 'value' => false, 'created_by' => 1, 'updated_by' => 1, 'is_delete' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }

        foreach($values as $v){
            \DB::table('comments_control')->insert($v);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments_control');
    }
}
