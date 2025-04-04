<?php namespace App\SReport;
    
use App\Models\cutCalendarQ;
use App\Models\week_cut;
use App\SData\SDataProcess;
use App\SUtils\SDateTimeUtils;
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
        try {
            // Obtener colección con los datos del reporte
            $lData = SReportUtils::getJourneyData($sConfiguration, $sReference);
            if (is_string($lData)) {
                return $lData;
            }
            // Obtener objeto de configuración
            $oConfiguration = SReportUtils::getReportConfigObj($sConfiguration);
            if (is_string($oConfiguration)) {
                return $oConfiguration;
            }
            // Obtener el tipo de pago
            $sPayTypeText = SReportUtils::getPayTypeText($oConfiguration->pay_type);
            // Obtener la fecha de inicio y fin del reporte
            $aDates = SReportUtils::getStartAndEndDate($oConfiguration, $sReference);
            if (is_string($aDates)) {
                return $aDates;
            }
            $sStartDate = $aDates[0];
            $sEndDate = $aDates[1];
            // Obtener la configuración de columnas
            $aColumns = SReportUtils::getColumns($oConfiguration);

            /**
             * ***********************************************************************************************************
             * Sección para pruebas
             */
            // $oStartDate = Carbon::parse($sStartDate)->locale('es');
            // $oEndDate = Carbon::parse($sEndDate)->locale('es');
            // $sPeriod = "";
            // // Configurar periodo para cuando las fechas sean del mismo mes
            // if ($oStartDate->month == $oEndDate->month) {
            //     $sPeriod = $oStartDate->format('d') . " al "
            //                     . $oEndDate->format('d') . " "
            //                     . $oStartDate->shortMonthName . ". "
            //                     . $oStartDate->year;
            // }
            // else {
            //     $sPeriod = $oStartDate->format('d') . " " . $oStartDate->shortMonthName . ". "
            //                 . $oStartDate->year. " al "
            //                 . $oEndDate->format('d') . " " . $oEndDate->shortMonthName . ". "
            //                 . $oEndDate->year;
            // }

            // return view('mails.journeyreport')->with('sStartDate', $sStartDate)
            //                                 ->with('sEndDate', $sEndDate)
            //                                 ->with('sPayTypeText', $sPayTypeText)
            //                                 ->with('sPeriod', $sPeriod)
            //                                 ->with('aColumns', $aColumns)
            //                                 ->with('lData', $lData);
            /**
             * ***********************************************************************************************************
             */
            
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

            $oMail->send(new JourneyReportNotification($sStartDate, $sEndDate, $sPayTypeText, $lData, $aColumns));

            return "";
        }
        catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();    
        }
    }

    public static function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function getJourneyData($sStartDate, $sEndDate, $iPayType, $aCompanies, $aAreas, $aDeptosCap, $aDeptosSiie, $aEmployees, $aBenPolicy)
    {
        $lEmployees = SGenUtils::getEmployeesByCfg($iPayType, $aCompanies, $aAreas, $aDeptosCap, $aDeptosSiie, $aEmployees, $aBenPolicy);
        $lEmployees = SReportsUtils::filterEmployeesByAdmissionDate($lEmployees, $sEndDate, 'id');
        $comments = null;
        $data53 = SDataProcess::getSchedulesAndChecks($sStartDate, $sEndDate, $iPayType, $lEmployees, $comments);
        $aEmps = $lEmployees->pluck('id');
        $lWorkshifts = SDelayReportUtils::getWorkshifts($sStartDate, $sEndDate, $iPayType, $aEmps);
        $lData53_2 = SDataProcess::addEventsDaysOffAndHolidays($data53, $lWorkshifts, $comments);
        $aEmployeeOverTime = $lEmployees->pluck('policy_extratime_id', 'id');
        // Se modifica la columna de política de tiempo extra para que el proceso lo calcule
        $aEmployeeOverTime = $aEmployeeOverTime->map(function ($item, $key) {
            return \SCons::ET_POL_ALWAYS;
        });
        $lData = SDataProcess::addDelaysAndOverTime($lData53_2, $aEmployeeOverTime, $sEndDate, $comments);
        $lDataWkd = SJourneyReport::addWorkedTime($lData);
        $lDataTxts = SJourneyReport::addEventsText($lDataWkd);
        $departments = $lEmployees->pluck('department_name', 'id');
        $jobs = $lEmployees->pluck('job_name', 'id');
        $lEmpDept = $departments->map(function ($department, $id) use ($jobs) {
            $jobNameDep = mb_convert_case($department, MB_CASE_TITLE, "UTF-8");
            $jobNameJob = mb_convert_case($jobs[$id], MB_CASE_TITLE, "UTF-8");
            return htmlspecialchars('Departamento: ' . $jobNameDep . ' - Puesto: ' . $jobNameJob, ENT_QUOTES, 'UTF-8');
        });
        $lDataDept = SJourneyReport::addDepartmentName($lDataTxts, $lEmpDept);
        $lDataFinal = SJourneyReport::groupData($lDataDept);

        // dd($lDataFinal);
        return $lDataFinal;
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
     * Determina los comentarios que se mostrarán en el reporte.
     * 
     * @param \Illuminate\Database\Eloquent\Collection $lData
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function addEventsText($lData)
    {
        foreach ($lData as $oRow) {
            $text = "";
            if ($oRow->others == "") {
                $text = $oRow->comments;
            }
            else {
                $text = $oRow->others;
            }

            $text = str_replace("Revisar horario. ", "", $text);
            $oRow->eventsText = htmlspecialchars(SJourneyReport::toMayusCase(strtolower($text)));
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
        // Variables para almacenar el ID del empleado actual, la lista de empleados, y la fila del empleado actual
        $idEmployee = 0;
        $lEmpRows = [];
        $oEmpRow = null;

        // Variables para almacenar el total de retrasos y tiempo adicional
        $totalDelay = 0;
        $totalAditional = 0;

        // Variable para determinar si se encontró un horario válido
        $bWithSchedule = false;

        // Iterar sobre cada fila de datos
        foreach ($lData as $oRow) {
            // Si el ID del empleado actual es diferente al anterior, procesar la fila del empleado anterior
            if ($oRow->idEmployee != $idEmployee) {
                $idEmployee = $oRow->idEmployee;
                if ($oEmpRow !== null) {
                    // Asignar los totales calculados a la fila del empleado
                    $oEmpRow->totalDelay = $totalDelay;
                    $oEmpRow->totalAditional = $totalAditional;
                    $lEmpRows[] = $oEmpRow; // Agregar la fila del empleado a la lista
                }

                // Crear una nueva fila de empleado con los datos actuales
                $oEmpRow = (object)[
                    'idEmployee' => $oRow->idEmployee,
                    'numEmployee' => $oRow->numEmployee,
                    'employee' => $oRow->employee,
                    'departmentName' => $oRow->departmentName,
                    'totalAditional' => 0,
                    'lRows' => [],
                    'schedule' => 'Sin horario' // Inicializar el horario como 'Sin horario'
                ];

                // Reiniciar los totales y el indicador de horario
                $totalDelay = 0;
                $totalAditional = 0;
                $bWithSchedule = false;
            }

            // Agregar la fila actual a la lista de filas del empleado
            $oEmpRow->lRows[] = $oRow;

            // Sumar los minutos de retraso y tiempo adicional
            $totalDelay += $oRow->entryDelayMinutes;
            $totalAditional += $oRow->overWorkedMins;

            // Verificar si hay un horario válido
            if (!$bWithSchedule && (strlen($oRow->outDateTime) > 11 || (strlen($oRow->inDateTime) == 10 && strlen($oRow->outDateTime) == 10))) {
                $date = $oRow->outDateTimeSch ?? ($oRow->outDate ?? null);
                if ($date !== null && $oRow->workable && SDateTimeUtils::dayOfWeek($date) != Carbon::SUNDAY && SDateTimeUtils::dayOfWeek($date) != Carbon::SATURDAY) {
                    if (strpos($oRow->eventsText, 'Sin horario') === false) {
                        if ($oRow->inDateTimeSch !== null && $oRow->outDateTimeSch !== null && strlen($oRow->inDateTimeSch) > 11 && strlen($oRow->outDateTimeSch) > 11) {
                            $oEmpRow->schedule = Carbon::parse($oRow->inDateTimeSch)->toTimeString() . ' - ' . Carbon::parse($oRow->outDateTimeSch)->toTimeString();
                            $bWithSchedule = true;
                        }
                    }
                }
            }
        }

        // Agregar la última fila del empleado a la lista, si existe
        if ($oEmpRow !== null) {
            $oEmpRow->totalDelay = $totalDelay;
            $oEmpRow->totalAditional = $totalAditional;
            $lEmpRows[] = $oEmpRow;
        }

        return $lEmpRows; // Retornar la lista de filas de empleados agrupadas
    }


    /**
     * Pone en mayúscula la primera letra después de cada punto y quita caracteres especiales
     *
     * @param string $string
     * @return string
     */
    private static function toMayusCase($string) {
        if ($string === "") {
            return "";
        }

        $text = trim($string);
        $last = substr($text, -1);
        $bPoint = $last === ".";

        if ($bPoint) {
            $text = substr($text, 0, -1);
        }

        $frases = explode(".", $text);
        $result = "";

        foreach ($frases as $frase) {
            $result .= ucfirst(trim($frase)) . ". ";
        }

        return htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
    }
}