<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;
    protected $changes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment, array $changes = [])
    {
        $this->appointment = $appointment;
        $this->changes = $changes;
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

        $mail = (new MailMessage)
            ->subject("Appointment Updated - {$appointment->appointment_number}")
            ->greeting("Hello {$patient->user->name},")
            ->line("Your appointment details have been updated.");

        if (!empty($this->changes)) {
            $mail->line("**Changes made:**");
            foreach ($this->changes as $field => $change) {
                $mail->line("• {$field}: {$change['old']} → {$change['new']}");
            }
        }

        $mail->line("**Updated Appointment Details:**")
            ->line("**Appointment Number:** {$appointment->appointment_number}")
            ->line("**Date:** {$appointment->appointment_date->format('l, F j, Y')}")
            ->line("**Time:** {$appointment->start_time->format('g:i A')} - {$appointment->end_time->format('g:i A')}")
            ->line("**Doctor:** Dr. {$doctor->user->name} ({$doctor->specialization})")
            ->line("**Clinic:** {$clinic->name}")
            ->line("**Status:** {$appointment->status}")
            ->line("**Type:** {$appointment->appointment_type}")
            ->when($appointment->reason, function ($mail) use ($appointment) {
                return $mail->line("**Reason:** {$appointment->reason}");
            })
            ->line("If you have any questions about these changes, please contact us.")
            ->action('View Appointment Details', url("/appointments/{$appointment->id}"))
            ->salutation("Best regards, {$clinic->name} Team");

        return $mail;
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
            'changes' => $this->changes,
            'appointment_date' => $this->appointment->appointment_date,
            'start_time' => $this->appointment->start_time,
            'end_time' => $this->appointment->end_time,
            'doctor_name' => $this->appointment->doctor->user->name,
            'clinic_name' => $this->appointment->clinic->name,
        ];
    }
}
