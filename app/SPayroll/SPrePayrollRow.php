<?php namespace App\SPayroll;

use Carbon\Carbon;

class SPrePayrollRow {
    public function __construct() {
        $this->employee_id = 0;
        $this->days = [];
    }
}

?>