<?php namespace App\SPayroll;

use Carbon\Carbon;

class SPrePayroll {
    public function __construct() {
        $this->start_date = null;
        $this->end_date = null;
        $this->rows = [];
    }
}

?>