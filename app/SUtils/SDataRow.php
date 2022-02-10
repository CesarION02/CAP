<?php namespace App\SUtils;

    class SDataRow {
        public function __construct() {
            $this->idEmployee = 0;
            $this->delayMins = 0;
            $this->absences = 0;
            $this->hasNoChecks = false;
            $this->lostBonus = false;
            $this->incidents = [];
            // $this->hasAbss = false;
        }
    }

?>