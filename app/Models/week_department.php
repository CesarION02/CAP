<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class week_department extends Model
{
    protected $table = 'week_department';
    protected $primaryKey = 'id';
    protected $fillable = ['week_id', 'department_id','status','group_id'];

    public function week(){
        return $this->belongsTo('App\Models\week','week_id');
    }

    public function department(){
        return $this->belongsTo('App\Models\department','department_id');
    }

    public function week_department_day(){
        return $this->HasMany('App\Models\week_department_day');
    }

    public function group(){
        return $this->belongsTo('App\Models\groupworkshift','group_id');
    }
}
