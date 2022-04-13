<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeVobo extends Model
{
    protected $table = 'prepayroll_report_emp_vobos';
    protected $primaryKey = 'id_vobo';

    protected $fillable = [
        'is_week',
        'num_week',
        'is_biweek',
        'num_biweek',
        'year',
        'is_delete',
        'employee_id',
        'vobo_by_id',
    ];

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id');
    }

    public function voboBy()
    {
        return $this->belongsTo('App\Models\User', 'vobo_by_id');
    }
}
