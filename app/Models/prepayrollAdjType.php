<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollAdjType extends Model
{
    protected $table = 'prepayroll_adjusts_types';
    protected $primaryKey = 'id';
    protected $fillable = ['type_code',
                            'type_name'];
}
