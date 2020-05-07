<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class workshift extends Model
{
    protected $table = 'workshifts';
    protected $primaryKey = 'id';
    protected $fillable = ['name','entry','departure','order','work_time','overtimepershift','cut_id'];

    public function day_workshifts(){
        return $this->hasMany('App\Models\day_workshifts');
    }
    public function groupworkshiftline(){
        return $this->hasMany('App\Models\groupworkshiftline');
    }
}
