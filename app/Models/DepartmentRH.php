<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentRH extends Model
{
    protected $table = 'dept_rh';
    protected $fillable = [
                            'code',
                            'name',
                            'external_id',
                            'is_delete'
                        ];
}
