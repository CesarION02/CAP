<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepayrollLog extends Model
{
    protected $table = 'prepayroll_report_logs';
    protected $primaryKey = 'id_log';
    protected $fillable = [
                            'id_generation',
                            'start_date',
                            'end_date',
                            'programmed_schedule_n',
                            'detected_schedule_n',
                            'adjust_by_system',
                            'register_n_id',
                            'type_reg_orig_n_id',
                            'way_pay_id',
                            'employee_id',
                            'user_by_id'
                        ];
}
