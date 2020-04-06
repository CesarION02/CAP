<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompaniesRegs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::table('companies')->insert([
        //     [ 'id' => '1', 'name' => 'OPERADORA TRON', 'fiscal_id' => '', 'is_delete' => '0', 'external_id' => '1211','created_by' => '1','updated_by' => '1'],
        //     [ 'id' => '2', 'name' => 'ACEITES ESPECIALES TH', 'fiscal_id' => '', 'is_delete' => '0', 'external_id' => '2852','created_by' => '1','updated_by' => '1'],
        //     [ 'id' => '3', 'name' => 'SOFTWARE APLICADO', 'fiscal_id' => '', 'is_delete' => '0', 'external_id' => '1603','created_by' => '1','updated_by' => '1'],
        //     [ 'id' => '4', 'name' => 'PRUEBAS Y', 'fiscal_id' => '', 'is_delete' => '0', 'external_id' => '2875','created_by' => '1','updated_by' => '1'],
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::table('companies')->where('id', '1')->delete();
        // DB::table('companies')->where('id', '2')->delete();
        // DB::table('companies')->where('id', '3')->delete();
        // DB::table('companies')->where('id', '4')->delete();
    }
}
