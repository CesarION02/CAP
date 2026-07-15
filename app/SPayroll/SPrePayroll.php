<?php namespace App\SPayroll;

use Carbon\Carbon;

class SPrePayroll {

    public $start_date;
    public $end_date;
    public $rows;
    public $processing_errors;
    
    public function __construct() {
        $this->start_date = null;
        $this->end_date = null;
        $this->rows = [];
        $this->processing_errors = [];
    }
}

?>