<?php

namespace App\Listeners;

use App\Events\AppointmentUpdated;
use App\Notifications\AppointmentUpdated as AppointmentUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAppointmentUpdatedNotification implements ShouldQueue
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
    public function handle(AppointmentUpdated $event): void
    {
        // Only send notification if there are actual changes
        if (!empty($event->changes)) {
            $event->appointment->patient->user->notify(new AppointmentUpdatedNotification($event->appointment, $event->changes));
        }
    }
}
