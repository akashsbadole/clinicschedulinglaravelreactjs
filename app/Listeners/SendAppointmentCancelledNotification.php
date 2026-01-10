<?php

namespace App\Listeners;

use App\Events\AppointmentCancelled;
use App\Notifications\AppointmentCancelled as AppointmentCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAppointmentCancelledNotification implements ShouldQueue
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
    public function handle(AppointmentCancelled $event): void
    {
        $event->appointment->patient->user->notify(new AppointmentCancelledNotification($event->appointment, $event->reason));
    }
}
