<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class firstDayYear extends Model
{
    protected $table = 'first_day_year';
    protected $primaryKey = 'id';
    protected $fillable = [
                            'year',
                            'dt_date',
                            'external_id',
                            'is_delete'
                        ];
    public $timestamps = false;
}
