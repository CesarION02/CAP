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
        'comments',
        'is_delete',
        'employee_id',
        'is_vobo',
        'vobo_by_id',
        'dt_vobo',
        'is_rejected',
        'rejected_by_id',
        'dt_rejected',
        'deleted_by_id',
        'dt_deleted',
        'created_at',
        'updated_at'
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
