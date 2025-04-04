<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\STasks\SRememberEmployeeNoCheck;

class SendRememberEmployeeNoCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:rememberEmployeeNoCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para enviar el email de recordatorio cuando empleado no checó';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SRememberEmployeeNoCheck::rememberCheckEmployee();
    }
}
