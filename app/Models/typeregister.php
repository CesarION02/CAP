<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class typeregister extends Model
{
    protected $table = 'type_register';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function register(){
        return $this->hasMany('App\register');
    }
}
