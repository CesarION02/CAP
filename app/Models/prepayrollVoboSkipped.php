<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollVoboSkipped extends Model
{
    protected $table = 'prepayroll_report_vobos_skipped';
    protected $primaryKey = 'id_skipped';
    protected $fillable = [
                            'is_week',
                            'num_week',
                            'is_biweek',
                            'num_biweek',
                            'year',
                            'dt_skipped',
                            'is_delete',
                            'skipped_by_id'
                        ];
}
