<?php

namespace App\Console\Commands;

use App\Jobs\SendOverduePaymentNotifications as SendOverduePaymentNotificationsJob;
use Illuminate\Console\Command;

class SendOverduePaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-overdue-notifications {--days=1 : Minimum days overdue to send notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send overdue payment notifications to patients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info("Sending overdue payment notifications for bills {$days}+ days overdue...");

        // Dispatch the job
        SendOverduePaymentNotificationsJob::dispatch($days);

        $this->info('Overdue payment notification job dispatched successfully.');
    }
}
