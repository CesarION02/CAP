<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdjustCommentsType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('prepayroll_adjusts_types')->insert([
        	['id' => '7','type_code' => 'COM','type_name' => 'COMENTARIOS'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('prepayroll_adjusts_types')->where('id', '7')->delete();
    }
}
