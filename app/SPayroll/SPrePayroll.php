<?php namespace App\SPayroll;

use Carbon\Carbon;

class SPrePayroll {

    public $start_date;
    public $end_date;
    public $rows;
    
    public function __construct() {
        $this->start_date = null;
        $this->end_date = null;
        $this->rows = [];
    }
}

?>