<?php namespace App\SReport;

use App\SReport\SReportUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResumeReportNotification;

class SResumeReport
{
    public static function executeResumeReport($sConfiguration)
    {
        try {
            // Obtener colección con los datos del reporte
            $lData = SReportUtils::getJourneyDataResume($sConfiguration);
            if (is_string($lData)) {
                return $lData;
            }
            // Obtener objeto de configuración
            $oConfiguration = SReportUtils::getReportConfigObj($sConfiguration);
            if (is_string($oConfiguration)) {
                return $oConfiguration;
            }

            /**
             * Dividir los 12 meses el año entre months_ago, para obtener fecha de inicio y fecha final de incidencias,
             * por ejemplo, si months_ago es 6, se dividirá el 12 / 6, obteniendo 2, lo que significa que se dividirá
             * el año en 2 periodos, y se obtendrá la fecha de inicio y fin de incidencias para cada periodo, determinar a qué periodo
             * corresponde start_date:
             */
            $oStartDate = Carbon::parse($oConfiguration->start_date);

            // Fecha de inicio del año correspondiente a start_date
            $oPeriodStart = Carbon::create($oStartDate->year, 1, 1);

            // Cantidad de meses a avanzar en cada iteración
            $monthsStep = $oConfiguration->months_ago;

            // Calculamos la fecha final del primer periodo
            $oPeriodEnd = $oPeriodStart->copy()->addMonths($monthsStep);

            // Número máximo de iteraciones para cubrir el año
            $maxIterations = intdiv(12, $monthsStep);

            for ($i = 0; $i < $maxIterations; $i++) {
                if ($oStartDate->between($oPeriodStart, $oPeriodEnd)) {
                    break;
                }

                // Avanzamos al siguiente período
                $oPeriodStart = $oPeriodEnd->copy();
                $oPeriodEnd = $oPeriodEnd->copy()->addMonths($monthsStep);
            }
        
            $sIncidentstartDate = $oPeriodStart->toDateString();
            $sIncidentsendDate = $oPeriodEnd->toDateString();

            // Agregar resumen de incidencias:
            $lData = SReportUtils::addIncidentsResume($lData, 
                                    $oConfiguration, 
                                    $sIncidentstartDate, 
                                    $sIncidentsendDate);

            // Obtener el tipo de pago
            $sPayTypeText = SReportUtils::getPayTypeText($oConfiguration->pay_type);
            // Obtener la fecha de inicio y fin del reporte
            $sStartDate = $oConfiguration->start_date;
            $sEndDate = $oConfiguration->end_date;
            // Obtener la configuración de columnas
            $aColumns = SReportUtils::getColumns($oConfiguration);

            $oStartDate = Carbon::parse($sStartDate)->locale('es');
            $oEndDate = Carbon::parse($sEndDate)->locale('es');
            $sPeriod = "";
            // Configurar periodo para cuando las fechas sean del mismo mes
            if ($oStartDate->month == $oEndDate->month) {
                $sPeriod = $oStartDate->format('d') . " al "
                                . $oEndDate->format('d') . " "
                                . $oStartDate->shortMonthName . " "
                                . $oStartDate->year;
            }
            else {
                $sPeriod = $oStartDate->format('d') . " " . $oStartDate->shortMonthName . " "
                            . $oStartDate->year. " al "
                            . $oEndDate->format('d') . " " . $oEndDate->shortMonthName . " "
                            . $oEndDate->year;
            }

             /**
             * ***********************************************************************************************************
             * Sección para pruebas
             */

            return view('mails.journeyresumereport')->with('sStartDate', $sStartDate)
                                            ->with('sEndDate', $sEndDate)
                                            ->with('incidentsStart', $sIncidentstartDate)
                                            ->with('incidentsEnd', $sIncidentsendDate)
                                            ->with('monthsAgo', $oConfiguration->months_ago)
                                            ->with('sPayTypeText', $sPayTypeText)
                                            ->with('sPeriod', $sPeriod)
                                            ->with('aColumns', $aColumns)
                                            ->with('lData', $lData);
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

            $oMail->send(new ResumeReportNotification($sStartDate, 
                                                        $sEndDate, 
                                                        $sPayTypeText, 
                                                        $lData, 
                                                        $aColumns, 
                                                        $oConfiguration->months_ago,
                                                        $sIncidentstartDate,
                                                        $sIncidentsendDate
                                                    ));

            return "";
        }
        catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();    
        }
    }
}