<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('num_employee');
            $table->integer('nip')->nullable();
            $table->integer('way_register_id')->unsigned();
            $table->integer('way_pay_id')->unsigned();
            $table->integer('job_id')->unsigned();
            $table->integer('is_delete')->default(0);

            $table->foreign('way_register_id')->references('id')->on('way_register')->onDelete('cascade');
            $table->foreign('way_pay_id')->references('id')->on('way_pay');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
