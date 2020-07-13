<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\admin\rol;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;
    protected $remember_token = false;
    protected $email_verified_at = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function roles()
    {
        return $this->belongsToMany(rol::class, 'user_rol');
    }

    public function setSession($roles)
    {
        if (count($roles) == 1) {
            Session::put(
                [
                    'rol_id' => $roles[0]['id'],
                    'rol_name' => $roles[0]['name'],
                    'user' => $this->email,
                    'user_id' => $this->id,
                    'name' => $this->name
                ]
            );
        }
    }
    public function group_user(){
        return $this->hasMany('App\Models\group_dept_user');
    }
    
}
