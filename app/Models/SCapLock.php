<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SCapLock extends Model
{
    protected $table = 'cap_locks';
    protected $fillable = [
        'got_at',
        'released_at',
        'timer',
        'completion_code',
        'lock_type',
        'user_id',
        'is_locked',
        'is_deleted',
    ];
}
