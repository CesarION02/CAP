<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class commentsControl extends Model
{
    protected $table = 'comments_control';
    protected $primaryKey = 'id_commentControl';
    protected $fillable = [
        'key_code',
        'Comment',
        'value',
        'created_by',
        'updated_by',
        'is_delete',
        'created_at',
        'updated_at'
    ];
}
