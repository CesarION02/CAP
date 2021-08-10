<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class department extends Model
{
    protected $table = 'departments';
    protected $fillable = ['name', 'area_id'];

    public function area(){
        return $this->belongsTo('App\Models\area','area_id');
    }

    public function rh(){
        return $this->belongsTo('App\Models\DepartmentRH','rh_department_id');
    }

    public function default_department(){
        return $this->hasMany('App\Models\DepartmentRH');
    }

    public function group(){
        return $this->belongsTo('App\Models\departmentsGroup','dept_group_id');
    }

    public function job(){
        return $this->hasMany('App\job');
    }

    public function employee(){
        return $this->hasMany('App\Models\employees');
    }

    public function week_department(){
        return $this->hasMany('App\Models\week_department');    
    }

    public function assign(){
        return $this->hasMany('App\Models\assing_schedule');
    }

    public function boss(){
        return $this->belongsTo('App\Models\employees','boss_id');
    }

}
