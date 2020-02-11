<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class groupworkshiftline extends Model
{
    protected $table = 'group_workshifts_lines';
    protected $primaryKey = 'id';
    protected $fillable = ['group_workshifts_id','workshifts_id'];

    public function groupworkshift(){
        return $this->belongsTo('App\Models\groupworkshift','group_workshift_id');
    }

    public function workshift(){
        return $this->belongsTo('App\Models\workshift','workshifts_id');
    }
}
