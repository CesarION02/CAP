<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class departmentsGroup extends Model
{
    protected $table = 'department_group';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];
    public $timestamps = false;
}
