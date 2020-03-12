<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class company extends Model
{
    function __construct() {
        $this->external_id = 0;
        $this->created_by = 1;
        $this->updated_by = 1;
    }

    protected $table = 'companies';
    protected $fillable = [
                            'name',
                            'fiscal_id',
                            'external_id',
                            'is_delete',
                            'created_by',
                            'updated_by',
                        ];
}
