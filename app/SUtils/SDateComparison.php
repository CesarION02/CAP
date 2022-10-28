<?php namespace App\SUtils;

/**
 * Estructura utilizada para la comparación de fechas
 */
class SDateComparison {
    public $variableDateTime;
    public $pinnedDateTime;
    public $diffMinutes;
    public $auxScheduleDay;
    public $auxWorkshift;
    public $auxIsSpecialSchedule;
    public $registry;
    public $withRegistry;

    function __construct() {
        $this->variableDateTime = null;
        $this->pinnedDateTime = null;

        $this->diffMinutes = 0;

        $this->auxScheduleDay = null;
        $this->auxWorkshift = null;
        $this->auxIsSpecialSchedule = false;

        $this->registry = null;

        $this->withRegistry = true;
    }
}

?>


