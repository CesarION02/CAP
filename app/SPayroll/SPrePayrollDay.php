<?php namespace App\SPayroll;

use Carbon\Carbon;

class SPrePayrollDay {
    public function __construct() {
        $this->dt_date = null;
        $this->entry = null;
        $this->leave = null;
        $this->prog_entry = null;
        $this->prog_leave = null;
        $this->holiday_id = 0;
    }
}

?>