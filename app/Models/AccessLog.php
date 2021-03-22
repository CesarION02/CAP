<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $table = 'access_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
                    'employee_id',
                    'dt_time_log',
                    'mins_in',
                    'mins_out',
                    'source',
                    'is_authorized',
                    'message',
                    'sch_in_dt_time',
                    'sch_out_dt_time',
                    'is_delete'
                ];
}
