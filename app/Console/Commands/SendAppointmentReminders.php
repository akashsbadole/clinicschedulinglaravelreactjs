<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentReminders as SendAppointmentRemindersJob;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-appointment-reminders {--hours=24 : Hours in advance to send reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders to patients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');

        $this->info("Sending appointment reminders for appointments {$hours} hours from now...");

        // Dispatch the job
        SendAppointmentRemindersJob::dispatch($hours);

        $this->info('Appointment reminder job dispatched successfully.');
    }
}
