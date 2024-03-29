<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

use App\SData\SDataAccessControl;

class BadCheckNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $employeeName;
    private $numEmployee;
    private $dtDateTime;
    private $reason;
    private $sSource;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($employeeName, $numEmployee, $dtDateTime, $reason, $sSource)
    {
        $this->employeeName = $employeeName;
        $this->numEmployee = $numEmployee;
        $this->dtDateTime = $dtDateTime;
        $this->reason = $reason;
        $this->sSource = $sSource;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('cap@swaplicado.com.mx')
                        ->subject('[CAP] Aviso Control de Acceso')
                        ->view('mails.badcheck')
                        ->with('employeeName', $this->employeeName)
                        ->with('numEmployee', $this->numEmployee)
                        ->with('dtDateTime', $this->dtDateTime)
                        ->with('reason', $this->reason)
                        ->with('sSource', $this->sSource);
    }
}
