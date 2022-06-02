<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRH extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_rh';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job',
        'acronym',
        'num_positions',
        'hierarchical_level',
        'is_deleted',
        'external_id',
        'dept_rh_id'
    ];

    /**
     * Obtiene el objeto departamento (Department) asociado al puesto
     *
     * @return Models/DepartmentRH
     */
    public function departmentRH()
    {
        return $this->belongsTo('App\Models\DepartmentRH', 'dept_rh_id');
    }
}
