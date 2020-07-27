<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class incidentDay extends Model
{
    protected $table = 'incidents_day';
    protected $primaryKey = 'id';
    protected $fillable = ['incidents_id',
                            'date date',
                            'num_day',
                            'is_delete'];
    
    public $timestamps = false;
}
