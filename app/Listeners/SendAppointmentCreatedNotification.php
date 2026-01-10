<?php

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\Notifications\AppointmentCreated as AppointmentCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAppointmentCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentCreated $event): void
    {
        // Send notification to the patient
        $event->appointment->patient->user->notify(new AppointmentCreatedNotification($event->appointment));
    }
}
