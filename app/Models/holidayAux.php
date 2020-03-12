<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class holidayAux extends Model
{
    protected $table = 'holidays_aux';
    protected $primaryKey = 'id';
    protected $fillable = [
                            'dt_date',
                            'text_description',
                            'is_delete',
                            'holiday_id',
                            'created_by',
                            'updated_by',
                            'created_at',
                            'updated_at',
                        ];

}
