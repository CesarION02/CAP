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

            $oIncidentsStartDate = Carbon::parse($oConfiguration->start_date)->subMonths($oConfiguration->months_ago);
            $oIncidentsEndDate = Carbon::parse($oConfiguration->start_date);
        
            $sIncidentstartDate = $oIncidentsStartDate->toDateString();
            $sIncidentsendDate = $oIncidentsEndDate->toDateString();

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

            // return view('mails.journeyresumereport')->with('sStartDate', $sStartDate)
            //                                 ->with('sEndDate', $sEndDate)
            //                                 ->with('incidentsStart', $sIncidentstartDate)
            //                                 ->with('incidentsEnd', $sIncidentsendDate)
            //                                 ->with('monthsAgo', $oConfiguration->months_ago)
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