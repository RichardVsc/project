<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $payerId;
    public int $recipientId;
    public float $amount;

    /**
     * Create a new event instance.
     *
     * @param int $payer
     * @param int $recipient
     * @param float $amount
     */
    public function __construct(int $payerId, int $recipientId, float $amount)
    {
        $this->payerId = $payerId;
        $this->recipientId = $recipientId;
        $this->amount = $amount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
