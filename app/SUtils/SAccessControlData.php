<?php namespace App\SUtils;

    class SAccessControlData {
        public $employee;
        public $absences;
        public $events;
        public $schedule;
        public $nextSchedule;
        public $authorized;
        public $message;
        public $employees;

        public function __construct() {
            $this->employee = null;
            $this->absences = null;
            $this->events = null;
            $this->schedule = null;
            $this->nextSchedule = null;
            $this->authorized = null;
            $this->message = null;
            $this->employees = null;
        }
    }

?>