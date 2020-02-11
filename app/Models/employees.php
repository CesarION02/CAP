<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class employees extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $fillable = ['name','num_employee','nip','way_register_id','job_id'];
    public $timestamps = false;

    public function job(){
        return $this->belongsTo('App\Models\job','job_id');
    }

    public function way_register(){
        return $this->belongsTo('App\Models\way_register', 'way_register_id');
    }

    public function incident(){
        return $this->hasMany('App\incident');
    }

    public function day_workshifts_employee(){
        return $this->hasMany('App\Models\day_workshifts_employee');
    }

    public function assign(){
        return $this->hasMany('App\Models\assing_schedule');
    }

}
