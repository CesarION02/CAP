<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class policyHoliday extends Model
{
    protected $table = 'policy_holidays';
    protected $primaryKey = 'id';
    
    public function area(){
        return $this->hasMany('App\Models\area');
    }

    public function department(){
        return $this->hasMany('App\Models\department');
    }

    public function job(){
        return $this->hasMany('App\Models\job');
    }
}
