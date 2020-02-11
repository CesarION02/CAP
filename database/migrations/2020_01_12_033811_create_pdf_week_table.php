<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePdfWeekTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_week', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('week_id')->unsigned();
            $table->string('url');
            $table->integer('is_delete')->default(0);
            $table->timestamps();

            $table->foreign('week_id')->references('id')->on('week')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdf_week');
    }
}
