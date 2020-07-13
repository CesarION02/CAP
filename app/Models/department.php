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

}
