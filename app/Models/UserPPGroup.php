<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPPGroup extends Model
{
    protected $table = 'prepayroll_groups_users';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
