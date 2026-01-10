<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bill;
    protected $amount;
    protected $paymentMethod;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bill $bill, float $amount, string $paymentMethod = null)
    {
        $this->bill = $bill;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
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

        $mail = (new MailMessage)
            ->subject("Payment Received - {$bill->bill_number}")
            ->greeting("Hello {$patient->user->name},")
            ->line("We have successfully received your payment.")
            ->line("**Payment Details:**")
            ->line("**Bill Number:** {$bill->bill_number}")
            ->line("**Amount Paid:** $" . number_format($this->amount, 2))
            ->line("**Payment Date:** " . now()->format('F j, Y'))
            ->when($this->paymentMethod, function ($mail) {
                return $mail->line("**Payment Method:** {$this->paymentMethod}");
            })
            ->line("**Bill Status:** " . ucfirst(str_replace('_', ' ', $bill->status)));

        if ($bill->status === 'paid') {
            $mail->line("Thank you for settling your account. Your bill is now fully paid.");
        } elseif ($bill->status === 'partially_paid') {
            $remaining = $bill->total_amount - $bill->amount; // This might need adjustment based on actual payment tracking
            $mail->line("**Remaining Balance:** $" . number_format($remaining, 2));
        }

        $mail->when($bill->appointment, function ($mail) use ($bill) {
                return $mail->line("**Related Appointment:** {$bill->appointment->appointment_number}");
            })
            ->when($doctor, function ($mail) use ($doctor) {
                return $mail->line("**Service Provider:** Dr. {$doctor->user->name}");
            })
            ->line("If you have any questions about this payment or need a receipt, please contact us.")
            ->action('View Bill Details', url("/bills/{$bill->id}"))
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
            'bill_id' => $this->bill->id,
            'bill_number' => $this->bill->bill_number,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'bill_status' => $this->bill->status,
            'clinic_name' => $this->bill->clinic->name,
        ];
    }
}
