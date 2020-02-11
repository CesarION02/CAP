<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class way_register extends Model
{
    protected $table = 'way_register';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function employee(){
        return $this->hasMany('App\Models\employees');
    }
}
