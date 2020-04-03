<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class holidayassign extends Model
{
    protected $table = 'holiday_assign';
    protected $primary = 'id';
    protected $fillable = ['department_id',
                            'employee_id',
                            'area_id',
                            'group_assign_id',
                            'holiday_id',
                            'date',
                            'is_delete'];

    public function department(){
        return $this->belongsTo('App\Models\department', 'department_id');   
    }

    public function employee(){
        return $this->belongsTo('App\Models\employees','employee_id');
    }

    public function holiday(){
        return $this->belongsTo('App\Models\holiday','holiday_id');
    }

    public function area(){
        return $this->belongsTo('App\Models\area','area_id');
    }

    public function group(){
        return $this->belongsTo('App\Models\group_assign','group_assing_id');
    }

}
