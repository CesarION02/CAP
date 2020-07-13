<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class group_dept_user extends Model
{
    protected $table = 'group_dept_user';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function group(){
        return $this->belongsTo('App\Models\departmentsGroup','groupdept_id');
    }

    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
}
