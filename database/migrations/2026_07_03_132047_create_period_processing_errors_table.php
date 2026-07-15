<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodProcessingErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_processing_errors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('period_processed_id');

            $table->text('errors');
            $table->longText('trace')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('period_processed_id')->references('id')->on('period_processed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('period_processing_errors');
    }
}
