<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $table = 'comments';
    protected $primaryKey =  'id';
    protected $fillable = [
        'comment',
        'is_delete',
        'created_by',
        'updated_by'
    ];

    public function userCreated(){
        return $this->hasOne('App\Models\User', 'id', 'created_by')->select('name');
    }
    public function userEdited(){
        return $this->hasOne('App\Models\User', 'id', 'updated_by')->select('name');
    }
}
