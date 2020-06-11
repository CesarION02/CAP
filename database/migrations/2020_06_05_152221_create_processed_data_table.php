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
            $table->date('sInDate');
            $table->date('inDate');
            $table->time('inDateTime');
            $table->time('inDateTimeNoficial');
            $table->date('outDate');
            $table->time('outDateTime');
            $table->time('outDateTimeNoficial');
            $table->double('diffMins');
            $table->double('delayMins');
            $table->double('overDefaultMins');
            $table->double('overScheduleMins');
            $table->double('overWorkedMins');
            $table->double('extraHours');
            $table->boolean('is_sunday');
            $table->boolean('is_dayoff');
            $table->boolean('is_holiday');
            $table->string('others');
            $table->string('comments');
            $table->double('extraDobleMins');
            $table->double('extraTripleMins');
            $table->double('extraDobleMinsNoficial');
            $table->double('extraTripleMinsNoficial');

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
