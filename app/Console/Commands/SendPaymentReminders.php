<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminders as SendPaymentRemindersJob;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-payment-reminders {--days=7 : Days in advance to send reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders to patients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info("Sending payment reminders for bills due in {$days} days...");

        // Dispatch the job
        SendPaymentRemindersJob::dispatch($days);

        $this->info('Payment reminder job dispatched successfully.');
    }
}
