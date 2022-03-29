<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollGroupDepartment extends Model
{
    protected $table = 'prepayroll_group_deptos';
    protected $primaryKey = 'id';
    protected $fillable = ['group_id',
                            'department_id',
                            'user_by_id'];
}
