<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class job extends Model
{
    protected $table = 'jobs';
    protected $fillable = ['name','department_id'];
    
    public function department(){
        return $this->belongsTo('App\Models\department');
    }

    public function employee(){
        return $this->hasMany('App\Models\employees');
    }

    public function policyHoliday(){
        return $this->belongsTo('App\Models\policyHoliday','policy_holiday_id');
    }

}
