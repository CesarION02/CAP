<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollGroupEmployee extends Model
{
    protected $table = 'prepayroll_group_employees';
    protected $primaryKey = 'id_group_employee';
    protected $fillable = ['group_id',
                            'employee_id',
                            'is_delete',
                            'created_by',
                            'updated_by'];
}
