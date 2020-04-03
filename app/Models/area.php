<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function department(){
        return $this->hasMany('App\Models\department');
    }

    public function holiday(){
        return $this->hasMany('App\Models\holidayassign');
    }
}
