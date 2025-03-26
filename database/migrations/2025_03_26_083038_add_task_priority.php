<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaskPriority extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // agregar columna a la tabla
        Schema::table('programmed_tasks', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('apply_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // eliminar columna de la tabla
        Schema::table('programmed_tasks', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
}
