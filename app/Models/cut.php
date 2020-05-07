<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cut extends Model
{
    protected $table = 'cut_ed';
    protected $fillable = ['name', 'code'];
    public $timestamps = false;
}
