<?php namespace App\SUtils;

use App\Http\Controllers\prePayrollController;
use App\SData\SDataProcess;

class SReportsUtils {

    public static function setAbsencesAndHolidays(int $idEmployee, string $date, $oRow) {
        $lAbsences = prePayrollController::searchAbsence($idEmployee, $date);
                
        if (sizeof($lAbsences) > 0) {
            foreach ($lAbsences as $absence) {
                $key = explode("_", $absence->external_key);

                $abs = [];
                $abs['id_emp'] = $key[0];
                $abs['id_abs'] = $key[1];
                $abs['nts'] = $absence->nts;
                $abs['type_name'] = $absence->type_name;
                $oRow->others = (isset($oRow->others) ? $oRow->others : "")."".$absence->type_name.". ";

                $oRow->events[] = $abs;
            }
        }

        $holidays = SDataProcess::getHolidays($date);

        if ($holidays != null) {
            $num = sizeof($holidays);
    
            if ($num > 0) {
                $oRow->isHoliday = $num;
                $oRow->others = $oRow->others.'Festivo. ';
            }
        }

        return $oRow;
    }

    public static function getCssClass($oRow)
    {
        if ((isset($oRow->events) && sizeof($oRow->events) > 0) || (isset($oRow->isHoliday) && $oRow->isHoliday > 0)) {
            return "events";
        }
    }

}

