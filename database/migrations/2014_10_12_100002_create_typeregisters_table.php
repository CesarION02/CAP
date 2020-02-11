  <?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTyperegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        DB::table('type_registers')->insert([
        	['id' => '1','name' => 'Entrada'],
        	['id' => '2','name' => 'Salida'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_registers');
    }
}
