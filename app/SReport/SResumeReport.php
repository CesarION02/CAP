<?php namespace App\SReport;

use App\SReport\SReportUtils;
use Carbon\Carbon;

class SResumeReport
{
    public static function executeResumeReport($sConfiguration, $sReference)
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

            // Agregar resumen de incidencias:
            $lData = SReportUtils::addIncidentsResume($lData, $oConfiguration);

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
            $oStartDate = Carbon::parse($sStartDate)->locale('es');
            $oEndDate = Carbon::parse($sEndDate)->locale('es');
            $sPeriod = "";
            // Configurar periodo para cuando las fechas sean del mismo mes
            if ($oStartDate->month == $oEndDate->month) {
                $sPeriod = $oStartDate->format('d') . " al "
                                . $oEndDate->format('d') . " "
                                . $oStartDate->shortMonthName . ". "
                                . $oStartDate->year;
            }
            else {
                $sPeriod = $oStartDate->format('d') . " " . $oStartDate->shortMonthName . ". "
                            . $oStartDate->year. " al "
                            . $oEndDate->format('d') . " " . $oEndDate->shortMonthName . ". "
                            . $oEndDate->year;
            }

            return view('mails.journeyresumereport')->with('sStartDate', $sStartDate)
                                            ->with('sEndDate', $sEndDate)
                                            ->with('sPayTypeText', $sPayTypeText)
                                            ->with('sPeriod', $sPeriod)
                                            ->with('aColumns', $aColumns)
                                            ->with('lData', $lData);
            /**
             * ***********************************************************************************************************
             */
            
            // $tos = explode(";", $oConfiguration->mails->to);
            // $oMail = Mail::to($tos);
            
            // if (strlen($oConfiguration->mails->cc) > 0) {
            //     $ccs = explode(";", $oConfiguration->mails->cc);
            //     $oMail->cc($ccs);
            // }
            
            // if (strlen($oConfiguration->mails->cco) > 0) {
            //     $cco = explode(";", $oConfiguration->mails->cco);
            //     $oMail->bcc($cco);
            // }

            // $oMail->send(new JourneyReportNotification($sStartDate, $sEndDate, $sPayTypeText, $lData, $aColumns));

            // return "";
        }
        catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();    
        }
    }
}