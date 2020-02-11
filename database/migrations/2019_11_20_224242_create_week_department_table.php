<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeekDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('week_department', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('week_id')->unsigned();
            $table->integer('department_id')->unsigned();
            $table->integer('status')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->integer('is_delete');
            $table->timestamps();

            $table->foreign('week_id')->references('id')->on('week')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('status')->references('id')->on('status_department')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('group_workshifts')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('week_department');
    }
}
