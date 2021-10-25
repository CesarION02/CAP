<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollAuthControl extends Model
{
    protected $table = 'prepayroll_auth_controls';
    protected $primaryKey = 'id_control';
    protected $fillable = ['prepayroll_adjust_id',
                            'user_auth_id',
                            'is_authorized',
                            'auth_date',
                            'is_delete',
                            'created_by',
                            'updated_by'];
}
