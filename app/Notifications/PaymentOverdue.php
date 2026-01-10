<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bill;
    protected $daysOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bill $bill, int $daysOverdue = 0)
    {
        $this->bill = $bill;
        $this->daysOverdue = $daysOverdue;
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
        $bill = $this->bill;
        $patient = $bill->patient;
        $doctor = $bill->doctor;
        $clinic = $bill->clinic;

        $overdueText = $this->daysOverdue > 0 ? "{$this->daysOverdue} days overdue" : "now due";

        return (new MailMessage)
            ->subject("Payment Overdue Notice - {$bill->bill_number}")
            ->greeting("Hello {$patient->user->name},")
            ->line("This is a reminder that payment for your bill is {$overdueText}.")
            ->line("**Bill Details:**")
            ->line("**Bill Number:** {$bill->bill_number}")
            ->line("**Bill Date:** {$bill->bill_date->format('F j, Y')}")
            ->line("**Due Date:** {$bill->due_date->format('F j, Y')}")
            ->line("**Total Amount:** $" . number_format($bill->total_amount, 2))
            ->line("**Amount Due:** $" . number_format($bill->total_amount - ($bill->amount ?? 0), 2))
            ->when($bill->appointment, function ($mail) use ($bill) {
                return $mail->line("**Related Appointment:** {$bill->appointment->appointment_number} ({$bill->appointment->appointment_date->format('M j, Y')})");
            })
            ->when($doctor, function ($mail) use ($doctor) {
                return $mail->line("**Service Provider:** Dr. {$doctor->user->name}");
            })
            ->line("**Clinic:** {$clinic->name}")
            ->line("Please make payment as soon as possible to avoid additional fees or service interruptions.")
            ->line("You can make payment online through our patient portal or contact us to arrange payment.")
            ->action('Make Payment', url("/bills/{$bill->id}/pay"))
            ->line("If you have already made this payment, please disregard this notice.")
            ->salutation("Best regards, {$clinic->name} Billing Department");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'bill_id' => $this->bill->id,
            'bill_number' => $this->bill->bill_number,
            'days_overdue' => $this->daysOverdue,
            'total_amount' => $this->bill->total_amount,
            'amount_due' => $this->bill->total_amount - ($this->bill->amount ?? 0),
            'due_date' => $this->bill->due_date,
            'clinic_name' => $this->bill->clinic->name,
        ];
    }
}
