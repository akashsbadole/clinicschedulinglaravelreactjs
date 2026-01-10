<?php

namespace App\Providers;

use App\Events\AppointmentCancelled;
use App\Events\AppointmentCreated;
use App\Events\AppointmentUpdated;
use App\Events\PaymentReceived;
use App\Listeners\SendAppointmentCancelledNotification;
use App\Listeners\SendAppointmentCreatedNotification;
use App\Listeners\SendAppointmentUpdatedNotification;
use App\Listeners\SendPaymentReceivedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AppointmentCreated::class => [
            SendAppointmentCreatedNotification::class,
        ],
        AppointmentUpdated::class => [
            SendAppointmentUpdatedNotification::class,
        ],
        AppointmentCancelled::class => [
            SendAppointmentCancelledNotification::class,
        ],
        PaymentReceived::class => [
            SendPaymentReceivedNotification::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
