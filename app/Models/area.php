<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    public function department(){
        return $this->hasMany('App\Models\department');
    }

    public function holiday(){
        return $this->hasMany('App\Models\holidayassign');
    }

    public function boss(){
        return $this->belongsTo('App\Models\employees','boss_id');
    }

    public function policyHoliday(){
        return $this->belongsTo('App\Models\policyHoliday','policy_holiday_id');
    }
}
