<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\admin\rol;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\CanResetPassword;
use App\Notifications\VerifyEmail;
use App\Notifications\PasswordReset;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;
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
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
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

    public function employee(){
        return $this->belongsTo('App\Models\employees','employee_id');
    }

    public function bitacora(){
        return $this->hasMany('App\Models\Bitacora');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }
    
}
