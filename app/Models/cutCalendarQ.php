<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cutCalendarQ extends Model
{
    protected $table = 'hrs_prepay_cut';
    protected $primaryKey = 'id';
    protected $fillable = ['external_id','num','dt_cut','year'];

}
