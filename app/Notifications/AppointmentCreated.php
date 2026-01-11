<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment Scheduled - ' . $this->appointment->clinic->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your appointment has been successfully scheduled.')
            ->line('**Appointment Details:**')
            ->line('**Date & Time:** ' . $this->appointment->scheduled_at->format('F j, Y \a\t g:i A'))
            ->line('**Doctor:** Dr. ' . $this->appointment->doctor->name)
            ->line('**Clinic:** ' . $this->appointment->clinic->name)
            ->line('**Type:** ' . $this->appointment->type)
            ->when($this->appointment->notes, function ($mail) {
                return $mail->line('**Notes:** ' . $this->appointment->notes);
            })
            ->action('View Appointment', url('/appointments/' . $this->appointment->id))
            ->line('If you need to reschedule or cancel, please contact us at least 24 hours in advance.')
            ->salutation('Best regards, ' . $this->appointment->clinic->name . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'type' => 'appointment_created',
            'message' => 'Your appointment has been scheduled for ' . $this->appointment->scheduled_at->format('F j, Y \a\t g:i A'),
        ];
    }
}
