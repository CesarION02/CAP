<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessedDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processed_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id')->unsigned();
            $table->date('inDate')->nullable();
            $table->time('inDateTime')->nullable();
            $table->time('inDateTimeNoficial')->nullable();
            $table->date('outDate')->nullable();
            $table->time('outDateTime')->nullable();
            $table->time('outDateTimeNoficial')->nullable();
            $table->double('diffMins')->nullable();
            $table->double('delayMins')->nullable();
            $table->double('overDefaultMins')->nullable();
            $table->double('overScheduleMins')->nullable();
            $table->double('overWorkedMins')->nullable();
            $table->double('extraHours')->nullable();
            $table->boolean('is_sunday')->nullable();
            $table->boolean('is_dayoff')->nullable();
            $table->boolean('is_holiday')->nullable();
            $table->string('others')->nullable();
            $table->string('comments')->nullable();
            $table->double('extraDobleMins')->nullable();
            $table->double('extraTripleMins')->nullable();
            $table->double('extraDobleMinsNoficial')->nullable();
            $table->double('extraTripleMinsNoficial')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processed_data');
    }
}
