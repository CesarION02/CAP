<?php namespace App\SPayroll;

class SPrePayrollDay {
    public $dt_date;
    public $num_absences;
    public $is_sunday;
    public $n_days_off;
    public $holiday_id;
    public $events;

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