<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class holiday extends Model
{
    protected $table = 'holidays';
    protected $primaryKey = 'id';
    protected $fillable = ['name','fecha','year'];

}
