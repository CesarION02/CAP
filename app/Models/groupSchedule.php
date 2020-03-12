<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class groupSchedule extends Model
{
    protected $table = 'group_schedule';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    public function assign(){
        return $this->hasMany('App\Models\assing_schedule');
    }

}
