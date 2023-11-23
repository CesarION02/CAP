<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class empVsPayroll extends Model
{
    protected $table = 'emp_vs_payroll';
    protected $primaryKey = 'id_empvspayroll';

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id');
    }

}