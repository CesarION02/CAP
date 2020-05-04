<?php namespace App\SPayroll;

class SPrePayrollRow {
    public function __construct() {
        $this->employee_id = 0;
        $this->double_overtime = 0.0;
        $this->triple_overtime = 0.0;
        $this->days = [];
    }
}

?>