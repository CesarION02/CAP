<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class schedule_day extends Model
{
    protected $table = 'schedule_day';
    protected $primary = 'id';
    protected $fillable = ['day_name','is_active','day_num','entry','departure','schedule_template_id'];

    public function schedule(){
        return $this->belongsTo('App\Models\schedule_template','schedule_template_id');
    }
}
