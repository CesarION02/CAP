<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class assign_schedule extends Model
{
    protected $table = 'schedule_assign';
    protected $primary = 'id';
    protected $fillable = ['department_id',
                            'employee_id',
                            'group_assign_id',
                            'schedule_template_id',
                            'start_date',
                            'end_date',
                            'group_schedules_id',
                            'order_gs',
                            'is_delete'];

    public function department(){
        return $this->belongsTo('App\Models\department', 'department_id');   
    }

    public function employee(){
        return $this->belongsTo('App\Models\employees','employee_id');
    }

    public function schedule(){
        return $this->belongsTo('App\Models\schedule_template','schedule_template_id');
    }

    public function group(){
        return $this->belongsTo('App\Models\group_assign','group_assing_id');
    }

}
