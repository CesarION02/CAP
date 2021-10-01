<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora_registers';
    protected $primaryKey = 'id';

    public function user(){
        return $this->belongsTo('App\Models\User','usuario_id');
    }

    public function register(){
        return $this->belongsTo('App\Models\register','register_id');
    }
}