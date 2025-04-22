<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class rememberVoboMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($wayPay, $endDate, $type, $num, $days, $sDateSubject)
    {
        $this->wayPay = $wayPay;
        $this->endDate = $endDate;
        $this->type = $type;
        $this->num = $num;
        $this->days = $days;
        $this->sDateSubject = $sDateSubject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $subject = '';
        if ($this->type == 'preClose') {
            $subject = 'Aviso: Corte prenómina ' . $this->wayPay . ' #' . $this->num . ': ' . $this->sDateSubject;
        } elseif ($this->type == 'afterClose') {
            $subject = 'Aviso: Falta Vobo de prenómina ' . $this->wayPay . ' #' . $this->num;
        }

        return $this->from('adrian.aviles@swaplicado.com.mx')
                    ->subject('[CAP] ' . $subject)
                    ->view('mails.rememberVobo')
                    ->with([
                        'wayPay' => $this->wayPay,
                        'endDate' => $this->endDate,
                        'type' => $this->type,
                        'num' => $this->num,
                        'days' => $this->days
                    ]);
    }
}
