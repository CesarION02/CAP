<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class groupworkshift extends Model
{
    protected $table = 'group_workshifts';
    protected $primary = 'id';
    protected $fillable = ['name'];
    
    public function line(){
        return $this->hasMany('App\Models\groupworkshiftline');
    }

    public function week_department(){
        return $this->hasMany('App\Models\week_department');
    }
}
