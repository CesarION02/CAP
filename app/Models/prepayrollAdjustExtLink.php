<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollAdjustExtLink extends Model
{
    protected $table = 'prepayroll_adjusts_ext_links';
    protected $primaryKey = 'id';

    protected $fillable = [
        'prepayroll_adjust_id',
        'external_key',
        'external_system'
    ];
}
