<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\SUtils\SDateFormatUtils;

class checadorVsNominaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file, $start_date, $end_date)
    {
        $this->file = $file;
        $this->start_date = $start_date; 
        $this->end_date = $end_date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->start_date = SDateFormatUtils::formatDate($this->start_date, 'D/mm/Y');
        $this->end_date = SDateFormatUtils::formatDate($this->end_date, 'D/mm/Y');

        $email = "adrian.aviles.swaplicado@gmail.com";
        return $this->from($email)
                        ->subject('Checador vs nomina')
                        ->attach($this->file, ['as' => 'archivo.xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->view('mails.checadorVsNominia')
                        ->with('ini', $this->start_date)
                        ->with('end', $this->end_date);
    }
}
