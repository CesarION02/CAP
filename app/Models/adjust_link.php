<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class adjust_link extends Model
{
    protected $table = 'adjust_link';
    protected $primaryKey = 'id';
    public $timestamps = false;


    public function adjust(){
        return $this->belongsTo('App\Models\prepayrollAdjust','adjust_id');
    }

    public function specialworkshift(){
        return $this->belongsTo('App\Models\specialworkshift','special_id');
    }

    public function incident(){
        return $this->belongsTo('App\Models\incident','incident_id');
    }
    

}
