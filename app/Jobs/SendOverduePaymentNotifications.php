<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Notifications\PaymentOverdue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOverduePaymentNotifications implements ShouldQueue
{
    use Queueable;

    protected $minimumDaysOverdue;

    /**
     * Create a new job instance.
     */
    public function __construct(int $minimumDaysOverdue = 1)
    {
        $this->minimumDaysOverdue = $minimumDaysOverdue;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = now()->toDateString();

        // Find bills that are overdue and not yet paid
        $bills = Bill::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', $today)
            ->get();

        Log::info("Sending overdue payment notifications for {$bills->count()} bills");

        foreach ($bills as $bill) {
            $daysOverdue = now()->diffInDays($bill->due_date);

            // Only send if overdue by at least the minimum days
            if ($daysOverdue >= $this->minimumDaysOverdue) {
                try {
                    $bill->patient->user->notify(new PaymentOverdue($bill, $daysOverdue));
                    Log::info("Sent overdue notification for bill {$bill->bill_number} ({$daysOverdue} days overdue)");
                } catch (\Exception $e) {
                    Log::error("Failed to send overdue notification for bill {$bill->bill_number}: {$e->getMessage()}");
                }
            }
        }
    }
}
