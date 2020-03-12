<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class benefitsPolice extends Model
{
    protected $table = 'benefit_policies';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'description'];
    public $timestamps = false;
}
