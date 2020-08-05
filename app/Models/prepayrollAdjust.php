<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollAdjust extends Model
{
    protected $table = 'prepayroll_adjusts';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id',
                            'dt_date',
                            'dt_time',
                            'minutes',
                            'apply_to',
                            'comments',
                            'is_delete',
                            'adjust_type_id',
                            'created_by',
                            'updated_by'];
}
