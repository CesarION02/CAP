<?php namespace App\SUtils;

/**
 * Estructura Utilizada para los renglones de los reporte de 
 * retardos y horas extras
 */
class SRegistryRow {
    function __construct() {
        $this->idEmployee = 0;
        $this->numEmployee = 0;
        $this->employee = 0;
        $this->inDate = null;
        $this->inDateTime = null;
        $this->outDate = null;
        $this->outDateTime = null;
        $this->outDateTimeSch = null;
        $this->delayMins = null;
        $this->extraHours = "00:00";
        $this->isSunday = 0;
        $this->others = "";
        $this->comments = "";
        $this->extraDouble = "00:00";
        $this->extraDoubleMins = 0;
        $this->extraTriple = "00:00";
        $this->extraTripleMins = 0;
    }
}

?>


