<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders implements ShouldQueue
{
    use Queueable;

    protected $hoursInAdvance;

    /**
     * Create a new job instance.
     */
    public function __construct(int $hoursInAdvance = 24)
    {
        $this->hoursInAdvance = $hoursInAdvance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $targetDateTime = now()->addHours($this->hoursInAdvance);

        // Find appointments that are scheduled within the next hour after the target time
        $appointments = Appointment::with(['patient.user', 'doctor.user', 'clinic'])
            ->where('status', 'scheduled')
            ->where('appointment_date', $targetDateTime->toDateString())
            ->where('start_time', '>=', $targetDateTime->toTimeString())
            ->where('start_time', '<', $targetDateTime->copy()->addHour()->toTimeString())
            ->get();

        Log::info("Sending appointment reminders for {$appointments->count()} appointments {$this->hoursInAdvance} hours in advance");

        foreach ($appointments as $appointment) {
            try {
                $appointment->patient->user->notify(new AppointmentReminder($appointment, $this->hoursInAdvance));
                Log::info("Sent reminder for appointment {$appointment->appointment_number}");
            } catch (\Exception $e) {
                Log::error("Failed to send reminder for appointment {$appointment->appointment_number}: {$e->getMessage()}");
            }
        }
    }
}
