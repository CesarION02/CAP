<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class departmentsGroup extends Model
{
    protected $table = 'department_group';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function group_user(){
        return $this->hasMany('App\Models\group_dept_user');
    }

    public function department(){
        return $this->hasMany('App\Models\department');
    }
}
