<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class modifyAdjusts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // inserta un nuevo tipo de ajuste
        DB::table('prepayroll_adjusts_types')->insert([
            'id' => 8,
            'type_code' => 'JSA',
            'type_name' => 'JUSTIFICAR SALIDA ANTICIPADA'
        ]);

        // agrega el campo is_external en la tabla prepayroll_adjusts
        Schema::table('prepayroll_adjusts', function (Blueprint $table) {
            $table->boolean('is_external')->default(false)->after('is_delete');
        });

        // crea la tabla prepayroll_adjusts_ext_links
        Schema::create('prepayroll_adjusts_ext_links', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('prepayroll_adjust_id')->unsigned();
            $table->string('external_key');
            $table->enum('external_system', ['pgh', 'siie']);
            $table->timestamps();

            $table->foreign('prepayroll_adjust_id')->references('id')->on('prepayroll_adjusts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // elimina el renglón insertado
        DB::table('prepayroll_adjusts_types')->where('id', 8)->delete();

        // elimina el campo is_external de la tabla prepayroll_adjusts
        Schema::table('prepayroll_adjusts', function (Blueprint $table) {
            $table->dropColumn('is_external');
        });

        // elimina la tabla prepayroll_adjusts_ext_links
        Schema::dropIfExists('prepayroll_adjusts_ext_links');
    }
}
