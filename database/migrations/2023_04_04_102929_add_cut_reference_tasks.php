<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCutReferenceTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programmed_tasks', function (Blueprint $table) {
            $table->string('reference_id', 50)->default("")->after('cfg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programmed_tasks', function (Blueprint $table) {
            $table->dropColumn('reference_id');
        });
    }
}
