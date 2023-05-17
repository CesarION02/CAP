<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRejectEmpVobos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayroll_report_emp_vobos', function (Blueprint $table) {
            // agregar columna comments a la tabla prepayroll_report_emp_vobos
            $table->string('comments', 255)->after('year')->default("");
            // agregar columna is_vobo
            $table->boolean('is_vobo')->after('employee_id')->default(false);
            // agregar columna is_rejected
            $table->boolean('is_rejected')->after('dt_vobo')->default(false);
            // agregar columna rejected_by
            $table->integer('rejected_by_id')->after('is_rejected')->nullable()->unsigned();
            // agregar columna dt_rejected
            $table->dateTime('dt_rejected')->after('rejected_by_id')->nullable();
            // agregar foreign key rejected_by_id
            $table->foreign('rejected_by_id')->references('id')->on('users');
        });

        DB::statement('ALTER TABLE prepayroll_report_emp_vobos 
                DROP FOREIGN KEY prepayroll_report_emp_vobos_vobo_by_id_foreign;
                ALTER TABLE prepayroll_report_emp_vobos 
                CHANGE COLUMN vobo_by_id vobo_by_id INT(10) UNSIGNED NULL;
                ALTER TABLE prepayroll_report_emp_vobos 
                ADD CONSTRAINT prepayroll_report_emp_vobos_vobo_by_id_foreign
                FOREIGN KEY (vobo_by_id)
                REFERENCES users (id);');

        DB::statement('ALTER TABLE prepayroll_report_emp_vobos 
                        CHANGE COLUMN dt_vobo dt_vobo DATETIME NULL; ');

        \App\Models\EmployeeVobo::where('is_delete', 0)
                                ->whereNotNull('dt_vobo')->update([
                                    'is_vobo' => 1
                                ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // eliminar columna comments
        Schema::table('prepayroll_report_emp_vobos', function (Blueprint $table) {
            $table->dropColumn('comments');
        });
        // eliminar columna is_vobo
        Schema::table('prepayroll_report_emp_vobos', function (Blueprint $table) {
            $table->dropColumn('is_vobo');
        });
        // eliminar columna is_rejected
        Schema::table('prepayroll_report_emp_vobos', function (Blueprint $table) {
            $table->dropColumn('is_rejected');
        });
        // eliminar foreign key rejected_by_id
        Schema::table('prepayroll_report_emp_vobos', function (Blueprint $table) {
            $table->dropForeign(['rejected_by_id']);
        });
        // eliminar columna rejected_by
        Schema::table('prepayroll_report_emp_vobos', function (Blueprint $table) {
            $table->dropColumn('rejected_by_id');
        });
        // eliminar columna dt_rejected
        Schema::table('prepayroll_report_emp_vobos', function (Blueprint $table) {
            $table->dropColumn('dt_rejected');
        });
    }
}
