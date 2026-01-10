<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Notifications\PaymentReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders implements ShouldQueue
{
    use Queueable;

    protected $daysInAdvance;

    /**
     * Create a new job instance.
     */
    public function __construct(int $daysInAdvance = 7)
    {
        $this->daysInAdvance = $daysInAdvance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $targetDate = now()->addDays($this->daysInAdvance)->toDateString();

        // Find bills that are due on the target date and are not yet paid
        $bills = Bill::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', $targetDate)
            ->get();

        Log::info("Sending payment reminders for {$bills->count()} bills {$this->daysInAdvance} days in advance");

        foreach ($bills as $bill) {
            try {
                $bill->patient->user->notify(new PaymentReminder($bill, $this->daysInAdvance));
                Log::info("Sent payment reminder for bill {$bill->bill_number}");
            } catch (\Exception $e) {
                Log::error("Failed to send payment reminder for bill {$bill->bill_number}: {$e->getMessage()}");
            }
        }
    }
}
