<?php namespace App\SReport;
    
use App\Models\cutCalendarQ;
use App\Models\week_cut;
use App\SData\SDataProcess;
use App\SUtils\SDelayReportUtils;
use App\SUtils\SGenUtils;
use App\SUtils\SRegistryRow;
use App\SUtils\SReportsUtils;
use App\Mail\JourneyReportNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SJourneyReport
{
    /**
     * En base a la configuración recibida procesa y envía el reporte de jornadas laborales.
     * Devuelve un string vacío si todo salió bien o un string con el error.
     * 
     * @param string $sConfiguration JSON con la configuración necesaria para la ejecución del reporte
     * {
        "$schema": "https://json-schema.org/draft/2020-12/schema",
        "type": "object",
        "properties": {
            "pay_type": {
            "type": "integer"
            },
            "back_prepayroll": {
            "type": "integer"
            },
            "companies": {
            "type": "array",
            "items": {
                "type": "integer"
            }
            },
            "areas": {
            "type": "array",
            "items": false
            },
            "departments_cap": {
            "type": "array",
            "items": {
                "type": "integer"
            }
            },
            "departments_siie": {
            "type": "array",
            "items": {
                "type": "integer"
            }
            },
            "employees": {
            "type": "array",
            "items": false
            },
            "benefit_policies": {
            "type": "array",
            "items": false
            },
            "mails": {
            "type": "object",
            "properties": {
                "to": {
                "type": "string",
                "format": "email"
                },
                "cc": {
                "type": "string",
                "format": "email"
                },
                "cco": {
                "type": "email"
                }
            },
            "required": [
                "to",
                "cc",
                "cco"
            ]
            }
        },
        "required": [
            "pay_type",
            "back_prepayroll",
            "companies",
            "areas",
            "departments_cap",
            "departments_siie",
            "employees",
            "benefit_policies",
            "mails"
        ]
        }
     * @param string $sReference fecha en la que estaba programada la tarea
     * 
     * @return string con el error si ocurrió alguno o vacío si todo salió OK
     */
    public static function manageTaskReport($sConfiguration, $sReference)
    {
        // Validar si la cadena recibida es un JSON
        if (! SJourneyReport::isJson($sConfiguration)) {
            return "Error, la configuración recibida no es un string JSON.";
        }

        $oConfiguration = json_decode($sConfiguration);

        // Si la configuración de tipo de pago no es correcta, retorna error
        if ($oConfiguration->pay_type == 0 || $oConfiguration->pay_type == "") {
            return "Error, el tipo de pago en la configuración no es válido.";
        }

        if (strlen($oConfiguration->mails->to) == 0) {
            return "Error, los destinarios para el correo no son válidos.";
        }

        $sStartDate = "";
        $sEndDate = "";
        $sPayTypeText = "";
        try {
            // La referencia es un string con el tipo de pago _ id de corte (Ejem: Q_456)
            $numPP = substr($sReference, 2);
            if ($oConfiguration->pay_type == \SCons::PAY_W_Q) {
                $oCut = cutCalendarQ::find($numPP);
                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte con la referencia: " . $sReference;
                }
                $oDate = Carbon::parse($oCut->dt_cut);
                if ($oConfiguration->back_prepayroll > 0) {
                    $oDate->subDays(15 * $oConfiguration->back_prepayroll);
                }

                $lCuts = cutCalendarQ::where('dt_cut', '<=', $oDate->toDateString())
                                        ->where('is_delete', 0)
                                        ->orderBy('dt_cut', 'DESC')
                                        ->limit(2)
                                        ->get();

                if (count($lCuts) < 2) {
                    return "Error, no se encontró fecha de corte para el reporte programado.";
                }

                $sEndDate = $lCuts[0]->dt_cut;
                $sStartDate = Carbon::parse($lCuts[1]->dt_cut)->addDay()->toDateString();
                $sPayTypeText = "Quincena";
            }
            else {
                $oCut = week_cut::find($numPP);
                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte con la referencia: " . $sReference;
                }
                $oDate = Carbon::parse($oCut->fin);
                if ($oConfiguration->back_prepayroll > 0) {
                    $oDate->subDays(7 * $oConfiguration->back_prepayroll);
                }

                $oCut = week_cut::where('fin', '<=', $oDate->toDateString())
                            ->orderBy('fin', 'DESC')
                            ->first();

                if (is_null($oCut)) {
                    return "Error, no se encontró fecha de corte para el reporte programado.";
                }

                $sStartDate = $oCut->ini;
                $sEndDate = $oCut->fin;
                $sPayTypeText = "Semana";
            }

            $lData = SJourneyReport::getJourneyData($sStartDate, $sEndDate, $oConfiguration->pay_type, 
                                                    $oConfiguration->companies, 
                                                    $oConfiguration->areas, 
                                                    $oConfiguration->departments_cap, 
                                                    $oConfiguration->departments_siie, 
                                                    $oConfiguration->employees, 
                                                    $oConfiguration->benefit_policies);
            
            $tos = explode(";", $oConfiguration->mails->to);
            $oMail = Mail::to($tos);
            
            if (strlen($oConfiguration->mails->cc) > 0) {
                $ccs = explode(";", $oConfiguration->mails->cc);
                $oMail->cc($ccs);
            }
            
            if (strlen($oConfiguration->mails->cco) > 0) {
                $cco = explode(";", $oConfiguration->mails->cco);
                $oMail->bcc($cco);
            }

            $oMail->send(new JourneyReportNotification($sStartDate, $sEndDate, $sPayTypeText, $lData));

            return "";
        }
        catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();    
        }
    }

    private static function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
     }

    private static function getJourneyData($sStartDate, $sEndDate, $iPayType, $aCompanies, $aAreas, $aDeptosCap, $aDeptosSiie, $aEmployees, $aBenPolicy)
    {
        $lEmployees = SGenUtils::getEmployeesByCfg($iPayType, $aCompanies, $aAreas, $aDeptosCap, $aDeptosSiie, $aEmployees, $aBenPolicy);
        $lEmployees = SReportsUtils::filterEmployeesByAdmissionDate($lEmployees, $sEndDate, 'id');
        $comments = null;
        $data53 = SDataProcess::getSchedulesAndChecks($sStartDate, $sEndDate, $iPayType, $lEmployees, $comments);
        $aEmployeeOverTime = $lEmployees->pluck('policy_extratime_id', 'id');
        $lData = SDataProcess::addDelaysAndOverTime($data53, $aEmployeeOverTime, $sEndDate, $comments);
        $lData = SJourneyReport::addWorkedTime($lData);
        $lEmpDept = $lEmployees->pluck('dept_name', 'id');
        $lData = SJourneyReport::addDepartmentName($lData, $lEmpDept);
        $lData = SJourneyReport::groupData($lData);

        // dd($lData);
        return $lData;
    }

    /**
     * Determina el tiempo trabajado por día y lo agrega al renglón en la variable: workedTime.
     * El tiempo es un entero expresado en minutos, el cual es la diferencia entre ora de entrada y salida
     * 
     * @param array<SRegistryRow> $lData
     * 
     * @return array<SRegistryRow>
     */
    public static function addWorkedTime($lData)
    {
        foreach ($lData as $oRow) {
            if (strlen($oRow->inDateTime) > 11 && strlen($oRow->outDateTime) > 11) {
                $oComp = SDelayReportUtils::compareDates($oRow->inDateTime, $oRow->outDateTime);
                $oRow->workedTime = $oComp->diffMinutes;
            }
        }

        return $lData;
    }

    /**
     * Pone el nombre del departamento al primer renglón de cada empleado.
     * Agrega el nombre en la variable: departmentName
     *
     * @param array<SRegistryRow> $lData
     * @param \Illuminate\Support\Collection $lEmployees con llave id de empleado
     * 
     * @return array<SRegistryRow>
     */
    public static function addDepartmentName($lData, $lEmployees)
    {
        $idEmployee = 0;
        foreach ($lData as $oRow) {
            if ($oRow->idEmployee != $idEmployee) {
                $oRow->departmentName = $lEmployees[$oRow->idEmployee];
                $idEmployee = $oRow->idEmployee;
            }
        }

        return $lData;
    }

    /**
     * Agrupa el arreglo de datos por empleado y los agrega al elemento del empleado correspondiente
     * 
     * @param array<SRegistryRow> $lData
     * 
     * @return array<\stdClass>
     */
    public static function groupData($lData)
    {
        $idEmployee = 0;
        $lEmpRows = [];
        $oEmpRow = null;
        $totalDelay = 0;
        foreach ($lData as $oRow) {
            if ($oRow->idEmployee != $idEmployee) {
                $idEmployee = $oRow->idEmployee;
                if (! is_null($oEmpRow)) {
                    $oEmpRow->totalDelay = $totalDelay;
                    $lEmpRows[] = $oEmpRow;
                }

                $oEmpRow = new \stdClass();
                $oEmpRow->idEmployee = $oRow->idEmployee;
                $oEmpRow->numEmployee = $oRow->numEmployee;
                $oEmpRow->employee = $oRow->employee;
                $oEmpRow->departmentName = $oRow->departmentName;
                $oEmpRow->lRows = [];
                $totalDelay = 0;
            }

            $oEmpRow->lRows[] = $oRow;
            $totalDelay += $oRow->entryDelayMinutes;
        }

        if (! is_null($oEmpRow)) {
            $oEmpRow->totalDelay = $totalDelay;
            $lEmpRows[] = $oEmpRow;
        }

        return $lEmpRows;
    }
}