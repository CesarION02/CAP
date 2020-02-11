<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class day_workshifts extends Model
{
    protected $table = 'day_workshifts';
    protected $primaryKey = 'id';
    protected $fillable = ['name','day_id','workshift_id'];

    public function week_department_day(){
        return $this->belongsTo('App\Models\week_department_day','day_id');
    }

    public function workshift(){
        return $this->belongsTo('App\Models\workshift','workshift_id');
    }

    public function day_workshifts_employee(){
        return $this->hasMany('App\Models\day_workshifts_employee');
    }


}
