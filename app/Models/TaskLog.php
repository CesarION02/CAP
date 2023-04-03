<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $table = 'programmed_tasks_logs';
    protected $primaryKey = 'id_log';
}