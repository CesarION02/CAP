<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class typeincident extends Model
{
    protected $table = 'type_incidents';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function incident(){
        return $this->hasMany('App\Models\incident');
    }
}
