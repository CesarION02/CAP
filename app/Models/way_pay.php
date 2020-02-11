<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class way_pay extends Model
{
    protected $table = 'way_pay';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function employee(){
        return $this->hasMany('App\employee');
    }

}
