<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncidentsCapFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('type_incidents', function (Blueprint $table) {
            $table->boolean('is_cap_edit')->after('is_allowed')->default(false);
        });

        DB::table('type_incidents')
                ->whereIn('id', [
                    14,
                    15,
                    16,
                    17,
                    18,
                    19,
                    20,
                    21,
                    22,
                    23,
                    24, 
                    25
                ])
                ->update([
                    'is_cap_edit' => '1',
                ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_incidents', function (Blueprint $table) {
            $table->dropColumn('is_cap_edit');
        });
    }
}
