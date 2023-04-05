<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class JourneyReportNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $startDate;
    private $endDate;
    private $typePay;
    private $lData;
    private $sSubject;
    private $sPeriod;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($startDate, $endDate, $typePay, $lData)
    {
        $oStartDate = Carbon::parse($startDate)->locale('es');
        $oEndDate = Carbon::parse($endDate)->locale('es');

        $this->startDate = $oStartDate->format('dd/M/y');
        $this->endDate = $oEndDate->format('dd/M/y');
        $this->typePay = $typePay;
        $this->lData = $lData;

        $this->sSubject = "[CAP] Reporte E/S ";
        $this->sPeriod = "";
        // Configurar periodo para cuando las fechas sean del mismo mes
        if ($oStartDate->month == $oEndDate->month) {
            $this->sPeriod = $oStartDate->format('dd') . " al "
                            . $oEndDate->format('dd') . " "
                            . $oStartDate->shortMonthName . ". "
                            . $oStartDate->year;
            $this->sSubject .= $this->sPeriod;
        }
        else {
            $this->sPeriod = $oStartDate->format('dd') . " " . $oStartDate->shortMonthName . ". "
                        . $oStartDate->year. " al "
                        . $oEndDate->format('dd') . " " . $oEndDate->shortMonthName . ". "
                        . $oEndDate->year;
                        
            $this->sSubject .= $this->sPeriod;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('cap@aeth.mx', 'CAP')
                        ->subject($this->sSubject)
                        ->view('mails.journeyreport')
                        ->with('sPeriod', $this->sPeriod)
                        ->with('startDate', $this->startDate)
                        ->with('endDate', $this->endDate)
                        ->with('typePay', $this->typePay)
                        ->with('lData', $this->lData);
    }
}
