<?php namespace App\SUtils;

/**
 * Estructura utilizada para la comparación de fechas
 */
class SDateComparison {
    function __construct() {
        $this->variableDateTime = null;
        $this->pinnedDateTime = null;

        $this->diffMinutes = 0;

        $this->auxScheduleDay = null;
        $this->auxWorkshift = null;
    }
}

?>


