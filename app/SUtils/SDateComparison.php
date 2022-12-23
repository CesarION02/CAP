<?php namespace App\SUtils;
      use Carbon\Carbon;

/**
 * Estructura utilizada para la comparación de fechas
 */
class SDateComparison {
    /**
     * Fecha-hora checada
     *
     * @var Carbon
     */
    public $variableDateTime;
    /**
     * Horario programado
     *
     * @var Carbon
     */
    public $pinnedDateTime;
    public $diffMinutes;
    /**
     * Objeto de template
     *
     * @var \stdClass
     */
    public $auxScheduleDay;
    /**
     * Objeto de workshift
     *
     * @var \stdClass
     */
    public $auxWorkshift;
    public $auxIsSpecialSchedule;
    /**
     * Objeto de checada
     *
     * @var \stdClass
     */
    public $registry;
    public $withRegistry;
    public $is_night;
    public $entry;

    function __construct() {
        $this->variableDateTime = null;
        $this->pinnedDateTime = null;

        $this->diffMinutes = 0;

        $this->auxScheduleDay = null;
        $this->auxWorkshift = null;
        $this->auxIsSpecialSchedule = false;

        $this->registry = null;

        $this->is_night = false;
        $this->entry = null;
        $this->withRegistry = true;
    }
}

?>


