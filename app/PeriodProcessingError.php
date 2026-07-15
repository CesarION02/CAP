<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodProcessingError extends Model
{
    protected $table = 'period_processing_errors';

    protected $fillable = [
        'period_processed_id',
        'employee_id',
        'error',
        'trace'
    ];

    public function employee(){
        return $this->belongsTo('App\Models\employees','employee_id');
    }

    public function periodProcessed(){
        return $this->belongsTo('App\Models\period_processed','period_processed_id');
    }
}
