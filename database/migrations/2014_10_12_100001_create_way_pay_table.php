<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWayPayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('way_pay', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        DB::table('way_pay')->insert([
        	['id' => '1','name' => 'Quincena'],
        	['id' => '2','name' => 'Semana'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('way_pay');
    }
}
