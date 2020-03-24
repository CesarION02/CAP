<?php namespace App\SUtils;

class SRegistryRow {
    function __construct() {
        $this->idEmployee = 0;
        $this->numEmployee = 0;
        $this->employee = 0;
        $this->inDate = null;
        $this->inDateTime = null;
        $this->outDate = null;
        $this->outDateTime = null;
        $this->delayMins = null;
        $this->comments = "";
    }
}

?>


