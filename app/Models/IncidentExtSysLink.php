<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentExtSysLink extends Model
{
    protected $table = 'incident_ext_sys_links';
    protected $primaryKey = 'id_link';
    protected $fillable = [
                        'incident_id',
                        'external_key',
                        'external_system'
                    ];
}
