<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class RejectedVoboNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $userRejectName;
    private $prepayrollNum;
    private $startDate;
    private $endDate;
    private $wayPay;
    private $reason;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userReject, $prepayrollNum, $startDate, $endDate, $wayPay, $reason)
    {
        $this->userRejectName = $userReject;
        $this->prepayrollNum = $prepayrollNum;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->wayPay = $wayPay;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('CAP')
                        ->subject('[CAP] Aviso de rechazo de VOBO')
                        ->view('mails.voborejected')
                        ->with('userRejectName', $this->userRejectName)
                        ->with('prepayrollNum', $this->prepayrollNum)
                        ->with('startDate', $this->startDate)
                        ->with('endDate', $this->endDate)
                        ->with('wayPay', $this->wayPay)
                        ->with('reason', $this->reason);
    }
}
