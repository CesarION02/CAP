<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAdjVsComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('comments')
            ->whereIn('id', [4, 8])
            ->update([
                'is_delete' => '1',
            ]);                            

        Schema::create('prepayroll_adjusts_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sort_number');
            $table->bigInteger('comment_id')->unsigned();
            $table->integer('adjust_type_id')->unsigned();

            $table->foreign('comment_id')->references('id')->on('comments');
            $table->foreign('adjust_type_id')->references('id')->on('prepayroll_adjusts_types');
        });

        DB::table('prepayroll_adjusts_comments')->insert([
            // JUSTIFICAR ENTRADA
        	['sort_number' => '1', 'comment_id' => '16', 'adjust_type_id' => '1'],
        	['sort_number' => '2', 'comment_id' => '5', 'adjust_type_id' => '1'],
        	['sort_number' => '3', 'comment_id' => '12', 'adjust_type_id' => '1'],
        	['sort_number' => '4', 'comment_id' => '11', 'adjust_type_id' => '1'],

            // JUSTIFICAR SALIDA
        	['sort_number' => '1', 'comment_id' => '3', 'adjust_type_id' => '2'],
        	['sort_number' => '2', 'comment_id' => '6', 'adjust_type_id' => '2'],
        	['sort_number' => '3', 'comment_id' => '9', 'adjust_type_id' => '2'],
        	['sort_number' => '4', 'comment_id' => '10', 'adjust_type_id' => '2'],
        	['sort_number' => '5', 'comment_id' => '11', 'adjust_type_id' => '2'],

            // JUSTIFICAR RETARDO
        	['sort_number' => '1', 'comment_id' => '5', 'adjust_type_id' => '3'],
        	['sort_number' => '2', 'comment_id' => '12', 'adjust_type_id' => '3'],
        	['sort_number' => '3', 'comment_id' => '16', 'adjust_type_id' => '3'],

            // JUSTIFICAR FALTA
        	['sort_number' => '1', 'comment_id' => '10', 'adjust_type_id' => '4'],
        	['sort_number' => '2', 'comment_id' => '11', 'adjust_type_id' => '4'],

            // DESCONTAR TIEMPO EXTRA
        	// ['sort_number' => '1', 'comment_id' => '', 'adjust_type_id' => '5'],

            // AGREGAR TIEMPO EXTRA
        	['sort_number' => '1', 'comment_id' => '2', 'adjust_type_id' => '6'],
        	['sort_number' => '2', 'comment_id' => '7', 'adjust_type_id' => '6'],
        	['sort_number' => '3', 'comment_id' => '13', 'adjust_type_id' => '6'],
        	['sort_number' => '4', 'comment_id' => '14', 'adjust_type_id' => '6'],
        	['sort_number' => '5', 'comment_id' => '17', 'adjust_type_id' => '6'],
        	['sort_number' => '6', 'comment_id' => '15', 'adjust_type_id' => '6'],

            // COMENTARIOS
        	['sort_number' => '1', 'comment_id' => '2', 'adjust_type_id' => '7'],
        	['sort_number' => '2', 'comment_id' => '3', 'adjust_type_id' => '7'],
        	['sort_number' => '3', 'comment_id' => '5', 'adjust_type_id' => '7'],
        	['sort_number' => '4', 'comment_id' => '6', 'adjust_type_id' => '7'],
        	['sort_number' => '5', 'comment_id' => '7', 'adjust_type_id' => '7'],
        	['sort_number' => '6', 'comment_id' => '9', 'adjust_type_id' => '7'],
        	['sort_number' => '7', 'comment_id' => '10', 'adjust_type_id' => '7'],
        	['sort_number' => '8', 'comment_id' => '11', 'adjust_type_id' => '7'],
        	['sort_number' => '9', 'comment_id' => '12', 'adjust_type_id' => '7'],
        	['sort_number' => '10', 'comment_id' => '13', 'adjust_type_id' => '7'],
        	['sort_number' => '11', 'comment_id' => '14', 'adjust_type_id' => '7'],
        	['sort_number' => '12', 'comment_id' => '16', 'adjust_type_id' => '7'],
        	['sort_number' => '13', 'comment_id' => '17', 'adjust_type_id' => '7'],
        	['sort_number' => '14', 'comment_id' => '18', 'adjust_type_id' => '7'],
        	['sort_number' => '15', 'comment_id' => '15', 'adjust_type_id' => '7'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_adjusts_comments');

        DB::table('comments')
            ->whereIn('id', [4, 8])
            ->update([
                'is_delete' => '0',
            ]); 
    }
}
