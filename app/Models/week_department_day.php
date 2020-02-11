<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class week_department_day extends Model
{
    protected $table = 'week_department_day';
    protected $primaryKey = 'id';
    protected $fillable = ['date','week_department_id','status'];

    public function week_department(){
        return $this->belongsTo('App\Models\week_department','week_department_id');
    }

    public function day_workshifts(){
        return $this->hasMany('App\Models\day_woekshifts');
    }
}
