<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class specialworkshift extends Model
{
    protected $table = 'specialworkshift';
    protected $primaryKey = 'id';

    public function employee(){
        return $this->belongsTo('App\Models\employees','employee_id');
    }
}
