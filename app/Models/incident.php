<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class incident extends Model
{
    protected $table = 'incidents';
    protected $primaryKey = 'id';
    protected $fillable = ['type_incidents_id','start_date','end_date','employee_id','is_delete'];
    
    public function typeincident(){
        return $this->belongsTo('App\Models\typeincident','type_incidents_id');
    }

    public function employee(){
        return $this->belongsTo('App\Models\employees');
    }

    public function incidentDays()
    {
        return $this->hasMany('App\Models\incidentDay', 'incidents_id', 'id');
    }
}
