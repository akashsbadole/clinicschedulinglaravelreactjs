<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment, string $reason = null)
    {
        $this->appointment = $appointment;
        $this->reason = $reason;
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
            ->subject("Appointment Cancelled - {$appointment->appointment_number}")
            ->greeting("Hello {$patient->user->name},")
            ->line("We regret to inform you that your appointment has been cancelled.");

        if ($this->reason) {
            $mail->line("**Reason for cancellation:** {$this->reason}");
        }

        $mail->line("**Cancelled Appointment Details:**")
            ->line("**Appointment Number:** {$appointment->appointment_number}")
            ->line("**Original Date:** {$appointment->appointment_date->format('l, F j, Y')}")
            ->line("**Original Time:** {$appointment->start_time->format('g:i A')} - {$appointment->end_time->format('g:i A')}")
            ->line("**Doctor:** Dr. {$doctor->user->name} ({$doctor->specialization})")
            ->line("**Clinic:** {$clinic->name}")
            ->line("**Type:** {$appointment->appointment_type}")
            ->when($appointment->reason, function ($mail) use ($appointment) {
                return $mail->line("**Reason:** {$appointment->reason}");
            })
            ->line("If you would like to reschedule this appointment, please contact us to arrange a new time.")
            ->action('Schedule New Appointment', url('/appointments/create'))
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
            'reason' => $this->reason,
            'appointment_date' => $this->appointment->appointment_date,
            'start_time' => $this->appointment->start_time,
            'end_time' => $this->appointment->end_time,
            'doctor_name' => $this->appointment->doctor->user->name,
            'clinic_name' => $this->appointment->clinic->name,
        ];
    }
}
