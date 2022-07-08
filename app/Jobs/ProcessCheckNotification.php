<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\SData\SDataAccessControl;

class ProcessCheckNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $idEmployee;
    private $dtDateTime;
    private $sourceString;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $dtDateTime, $sSource)
    {
        $this->idEmployee = $id;
        $this->dtDateTime = $dtDateTime;
        $this->sourceString = $sSource;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        SDataAccessControl::evaluateToSend($this->idEmployee, $this->dtDateTime, $this->sourceString);
    }
}
