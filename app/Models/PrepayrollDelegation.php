<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PrepayrollDelegation extends Model
{
    protected $table = 'prepayroll_report_delegations';
    protected $primaryKey = 'id_delegation';

    protected $fillable = ['number_prepayroll',
                            'year',
                            'json_insertions',
                            'is_active',
                            'is_delete',
                            'pay_way_id',
                            'user_delegation_id',
                            'user_delegated_id',
                            'user_insert_id',
                            'user_update_id',
                        ];

    public function userDelegation()
    {
        return $this->belongsTo(User::class, 'user_delegation_id', 'id_user');
    }

    public function userDelegated()
    {
        return $this->belongsTo(User::class, 'user_delegated_id', 'id_user');
    }

    public function userInsert()
    {
        return $this->belongsTo(User::class, 'user_insert_id', 'id_user');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_update_id', 'id_user');
    }
}
