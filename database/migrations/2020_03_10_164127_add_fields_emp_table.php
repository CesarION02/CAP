<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsEmpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (blueprint $table) {
            $table->string('names')->default('')->after('name');
            $table->string('first_name')->default('')->after('names');
            $table->string('short_name')->default('')->after('first_name');
            $table->integer('external_id')->unsigned()->nullable()->after('job_id');
            $table->date('admission_date')->nullable()->after('num_employee');
            $table->date('leave_date')->nullable()->after('admission_date');
            $table->boolean('is_overtime')->default(false)->after('leave_date');
            $table->boolean('is_active')->default(false)->after('external_id');
            $table->integer('ben_pol_id')->unsigned()->default(1)->after('way_pay_id');

            $table->foreign('ben_pol_id')->references('id')->on('benefit_policies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function($table)
        {
            $table->dropColumn('names');
            $table->dropColumn('first_name');
            $table->dropColumn('short_name');
            $table->dropColumn('external_id');
            $table->dropColumn('admission_date');
            $table->dropColumn('leave_date');
            $table->dropColumn('is_overtime');
            $table->dropColumn('is_active');

            $table->dropForeign(['ben_pol_id']);
            $table->dropColumn('ben_pol_id');
        });
    }
}
