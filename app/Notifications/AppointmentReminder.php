<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;
    protected $hoursUntil;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment, int $hoursUntil = 24)
    {
        $this->appointment = $appointment;
        $this->hoursUntil = $hoursUntil;
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
        $appointment = $this->appointment;
        $patient = $appointment->patient;
        $doctor = $appointment->doctor;
        $clinic = $appointment->clinic;

        $reminderText = $this->hoursUntil === 24 ? 'tomorrow' : "in {$this->hoursUntil} hours";

        return (new MailMessage)
            ->subject("Appointment Reminder - {$appointment->appointment_number}")
            ->greeting("Hello {$patient->user->name},")
            ->line("This is a friendly reminder about your upcoming appointment {$reminderText}.")
            ->line("**Appointment Details:**")
            ->line("**Appointment Number:** {$appointment->appointment_number}")
            ->line("**Date:** {$appointment->appointment_date->format('l, F j, Y')}")
            ->line("**Time:** {$appointment->start_time->format('g:i A')} - {$appointment->end_time->format('g:i A')}")
            ->line("**Doctor:** Dr. {$doctor->user->name} ({$doctor->specialization})")
            ->line("**Clinic:** {$clinic->name}")
            ->line("**Address:** {$clinic->address}, {$clinic->city}, {$clinic->state} {$clinic->postal_code}")
            ->line("**Phone:** {$clinic->phone}")
            ->line("**Type:** {$appointment->appointment_type}")
            ->when($appointment->reason, function ($mail) use ($appointment) {
                return $mail->line("**Reason:** {$appointment->reason}");
            })
            ->line("Please arrive 15 minutes before your scheduled time.")
            ->line("If you need to reschedule or have any questions, please contact us at {$clinic->phone} or reply to this email.")
            ->when($clinic->website, function ($mail) use ($clinic) {
                return $mail->action('Visit Our Website', $clinic->website);
            })
            ->salutation("Best regards, {$clinic->name} Team");
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
            'appointment_number' => $this->appointment->appointment_number,
            'hours_until' => $this->hoursUntil,
            'appointment_date' => $this->appointment->appointment_date,
            'start_time' => $this->appointment->start_time,
            'end_time' => $this->appointment->end_time,
            'doctor_name' => $this->appointment->doctor->user->name,
            'clinic_name' => $this->appointment->clinic->name,
        ];
    }
}
