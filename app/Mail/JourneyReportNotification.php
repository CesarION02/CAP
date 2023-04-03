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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($startDate, $endDate, $typePay, $lData)
    {
        $this->startDate = Carbon::parse($startDate)->format('d/m/Y');
        $this->endDate = Carbon::parse($endDate)->format('d/m/Y');
        $this->typePay = $typePay;
        $this->lData = $lData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('cap@swaplicado.com.mx')
                        ->subject('[CAP] Reporte de Jornadas Laborales ('.$this->typePay.')')
                        ->view('mails.journeyreport')
                        ->with('startDate', $this->startDate)
                        ->with('endDate', $this->endDate)
                        ->with('typePay', $this->typePay)
                        ->with('lData', $this->lData);
    }
}
