<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentRH extends Model
{
    protected $table = 'dept_rh';
    protected $fillable = [
                            'code',
                            'name',
                            'external_id',
                            'is_delete'
                        ];
    
    public function department(){
        return $this->hasMany('App\department');
    }

    
    public function default_dept(){
        return $this->belongsTo('App\Models\department','default_dept_id');
    }
}
