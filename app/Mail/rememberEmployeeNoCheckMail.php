<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class rememberEmployeeNoCheckMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $date)
    {
        $this->type = $type;
        $this->date = $date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = '';
        switch ($this->type) {
            case 1:
                $subject = '[CAP] Aviso: sin entrada ' . $this->date;
                break;
            case 2:
                $subject = '[CAP] Aviso: sin salida ' . $this->date;
                break;
            case 3:
                $subject = '[CAP] Aviso: falta ' . $this->date;
                break;
            
            default:
                # code...
                break;
        }

        return $this->from('cap@aeth.mx')
                    ->subject($subject)
                    ->view('mails.rememberEmployeeNoCheck')
                    ->with([
                        'type' => $this->type,
                        'date' => $this->date
                    ]);
    }
}
