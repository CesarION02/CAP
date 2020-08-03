<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrepayrollAdjustsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepayroll_adjusts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id')->unsigned();
            $table->date('dt_date');
            $table->time('dt_time')->nullable();
            $table->integer('minutes');
            $table->integer('apply_to')->unsigned();
            $table->string('comments')->nullable()->default("");
            $table->boolean('is_delete');
            $table->integer('adjust_type_id')->unsigned();
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();

            $table->foreign('adjust_type_id')->references('id')->on('prepayroll_adjusts_types');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepayroll_adjusts');
    }
}
