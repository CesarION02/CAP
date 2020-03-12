<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class employees extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $fillable = [
                            'name',
                            'names',
                            'first_name',
                            'short_name',
                            'num_employee',
                            'admission_date date',
                            'leave_date date',
                            'is_overtime',
                            'company_id',
                            'nip',
                            'way_register_id',
                            'way_pay_id',
                            'ben_pol_id',
                            'job_id',
                            'external_id',
                            'is_active',
                            'is_delete',
                            ];
    public $timestamps = false;

    public function job(){
        return $this->belongsTo('App\Models\job','job_id');
    }

    public function way_register(){
        return $this->belongsTo('App\Models\way_register', 'way_register_id');
    }

    public function incident(){
        return $this->hasMany('App\incident');
    }

    public function day_workshifts_employee(){
        return $this->hasMany('App\Models\day_workshifts_employee');
    }

    public function assign(){
        return $this->hasMany('App\Models\assing_schedule');
    }

}
