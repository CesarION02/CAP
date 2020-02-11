<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class day_workshifts_employee extends Model
{
    protected $table = 'day_workshifts_employee';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id','day_id'];

    public function employee(){
        return $this->belongsTo('App\Models\employees','employee_id');
    }

    public function day(){
        return $this->belongsTo('App\Models\day_workshifts','day_id');
    }
}
