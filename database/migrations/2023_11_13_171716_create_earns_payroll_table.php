<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEarnsPayrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('earns_payroll', function (Blueprint $table) {
            $table->increments('id_earnspayroll');
            $table->integer('empvspayroll_id')->unsigned();
            $table->integer('ear_id')->unsigned();
            $table->double('unt');
            $table->timestamps();

            $table->foreign('empvspayroll_id')->references('id_empvspayroll')->on('emp_vs_payroll');
            $table->foreign('ear_id')->references('id_ear')->on('earnings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('earns_payroll');
    }
}
