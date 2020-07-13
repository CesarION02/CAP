<?php namespace App\SPayroll;

class SPrePayrollDay {
    public function __construct() {
        $this->dt_date = null;
        // $this->entry = [];
        // $this->leave = [];
        // $this->prog_entry = null;
        // $this->prog_leave = null;
        $this->num_absences = 0;
        $this->is_sunday = 0;
        $this->n_days_off = 0;
        $this->holiday_id = 0;
        $this->events = [];
    }
}

?>