<?php namespace App\SPayroll;

class SPrePayrollDay {
    public function __construct() {
        $this->dt_date = null;
        // $this->entry = [];
        // $this->leave = [];
        // $this->prog_entry = null;
        // $this->prog_leave = null;
        $this->is_absence = false;
        $this->is_sunday = false;
        $this->n_days_off = false;
        $this->holiday_id = 0;
        $this->events = [];
    }
}

?>