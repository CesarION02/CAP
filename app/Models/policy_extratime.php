<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class policy_extratime extends Model
{
    protected $table = 'policy_extratime';
    protected $primaryKey = 'id';

    protected $fillable = ['id','name'];

    public function employee(){
        return $this->hasMany('App\Models\employees');
    }
}
