<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class Roluser extends Model
{
    protected $table = "user_rol";
    protected $fillable = ['name'];
}
