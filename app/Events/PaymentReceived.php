<?php

namespace App\Events;

use App\Models\Bill;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bill;
    public $amount;
    public $paymentMethod;

    /**
     * Create a new event instance.
     */
    public function __construct(Bill $bill, float $amount, string $paymentMethod = null)
    {
        $this->bill = $bill;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('clinic.' . $this->bill->clinic_id),
        ];
    }
}
