<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class group_assign extends Model
{
    protected $table = 'group_assign';
    protected $primary = 'id';
    protected $fillable = ['is_delete'];

    public function assign(){
        return $this->hasMany('App\Models\assign_schedule');
    }

}
