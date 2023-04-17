<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class subtypeincident extends Model
{
    protected $table = 'type_sub_incidents';
    protected $primaryKey = 'id_sub_incident';
    protected $fillable = [
                'name',
                'is_default',
                'is_delete',
                'incident_type_id',
                'ext_pgh_id',
                'ext_siie_id'
            ];
}
