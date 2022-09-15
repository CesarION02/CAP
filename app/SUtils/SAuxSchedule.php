<?php namespace App\SUtils;

    class SAuxSchedule {
        public $inDateTimeSch;
        public $outDateTimeSch;
        public $isSpecialSchedule;

        public function __construct() {
            $this->inDateTimeSch = null;
            $this->outDateTimeSch = null;
            $this->isSpecialSchedule = false;
        }
    }

?>