<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class register extends Model
{
    protected $table = 'registers';
    protected $primaryKey = 'id';
    

    public function employee(){
        return $this->hasMany('App\employee');
    }

    public function typeregister(){
        return $this->belongsTo('App\typeregister');
    }
}
