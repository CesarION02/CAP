<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\PeriodProcessingError;

class period_processed extends Model
{
    protected $table = 'period_processed';

    public function errors(){
        return $this->hasMany(PeriodProcessingError::class);
    }
}
