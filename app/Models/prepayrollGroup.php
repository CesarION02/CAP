<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class prepayrollGroup extends Model
{
    protected $table = 'prepayroll_groups';
    protected $primaryKey = 'id_group';
    protected $fillable = ['group_code',
                            'group_name',
                            'head_user_id',
                            'father_group_n_id',
                            'is_delete',
                            'created_by',
                            'updated_by'];
}
