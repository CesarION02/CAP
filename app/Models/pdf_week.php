<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pdf_week extends Model
{
    protected $table = 'pdf_week';
    protected $primaryKey = 'id';
    protected $fillable = ['week_id', 'url'];

    public function week(){
        return $this->belongsTo('App\Models\week','week_id');
    }
}
