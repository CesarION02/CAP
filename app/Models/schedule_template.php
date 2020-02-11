<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class schedule_template extends Model
{
    protected $table = 'schedule_template';
    protected $primary = 'id';
    protected $fillable = ['name','is_delete'];

    public function day(){
        return $this->hasMany('App\Models\schedule_day');
    } 

    public function assign(){
        return $this->hasMany('App\Models\assing_schedule');
    }


}
