<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class processed_data extends Model
{
    protected $table = 'processed_data';

    public function employee(){
        return $this->belongsTo('App\Models\employees','employee_id');
    }
}
