<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class fingerprint extends Model
{
    protected $table = 'fingerprint';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function employee(){
        return $this->hasMany('App\employee');
    }
}
