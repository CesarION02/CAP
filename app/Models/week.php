<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class week extends Model
{
    protected $table = 'week';
    protected $primary = 'id';
    protected $fillable = ['week_number', 'year','start_date','end_date'];

    public function week_department(){
        return $this->hasMany('App\Models\week_department');    
    }

    public function pdf(){
        return $this->hasMany('App\Models\pdf_week');
    }
}
