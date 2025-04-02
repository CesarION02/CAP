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
    public function __construct($wayPay, $endDate, $type, $num, $days)
    {
        $this->wayPay = $wayPay;
        $this->endDate = $endDate;
        $this->type = $type;
        $this->num = $num;
        $this->days = $days;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('adrian.aviles.swaplicado@gmail.com')
                    ->subject('[CAP] Cierre prenómina ' . $this->wayPay . '  num. ' . $this->num)
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
