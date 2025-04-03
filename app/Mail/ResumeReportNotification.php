<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use \Log;

class ResumeReportNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $startDate;
    private $endDate;
    private $sPayTypeText;
    private $lData;
    private $sSubject;
    private $sPeriod;
    private $aColumns;
    private $monthsAgo;
    private $incidentsStart;
    private $incidentsEnd;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($startDate, $endDate, $sPayTypeText, $lData, $aColumns, $monthsAgo, $incidentsStart, $incidentsEnd)
    {
        $oStartDate = Carbon::parse($startDate)->locale('es');
        $oEndDate = Carbon::parse($endDate)->locale('es');

        $this->startDate = $oStartDate->format('dd/M/y');
        $this->endDate = $oEndDate->format('dd/M/y');
        $this->sPayTypeText = $sPayTypeText;
        $this->lData = $lData;
        $this->aColumns = $aColumns;
        $this->monthsAgo = $monthsAgo;
        $this->incidentsStart = $incidentsStart;
        $this->incidentsEnd = $incidentsEnd;

        $this->sSubject = "[CAP] Reporte Tiempo Laboral e Incidencias ";
        $this->sPeriod = "";

        // Configurar periodo para cuando las fechas sean del mismo mes
        if ($oStartDate->month == $oEndDate->month) {
            $this->sPeriod = $oStartDate->format('d') . " al "
                            . $oEndDate->format('d') . " "
                            . $oStartDate->shortMonthName . " "
                            . $oStartDate->year;
            // $this->sSubject .= $this->sPeriod;
        }
        else {
            $this->sPeriod = $oStartDate->format('d') . " " . $oStartDate->shortMonthName . " "
                        . $oStartDate->year. " al "
                        . $oEndDate->format('d') . " " . $oEndDate->shortMonthName . " "
                        . $oEndDate->year;
                        
            // $this->sSubject .= $this->sPeriod;
        }

        // obtener nombre del mes en base al $oStartDate
        $this->sSubject .= $oStartDate->shortMonthName . " " . $oStartDate->year;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info($this->sSubject);
        
        return $this->from('cap@aeth.mx', 'CAP')
                        ->subject($this->sSubject)
                        ->view('mails.journeyresumereport')
                        ->with('sPeriod', $this->sPeriod)
                        ->with('startDate', $this->startDate)
                        ->with('endDate', $this->endDate)
                        ->with('sPayTypeText', $this->sPayTypeText)
                        ->with('aColumns', $this->aColumns)
                        ->with('monthsAgo', $this->monthsAgo)
                        ->with('incidentsStart', $this->incidentsStart)
                        ->with('incidentsEnd', $this->incidentsEnd)
                        ->with('lData', $this->lData);
    }
}
