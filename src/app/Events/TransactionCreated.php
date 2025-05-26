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
    public int $amount;

    /**
     * Create a new event instance.
     *
     * @param int $payerId
     * @param int $recipientId
     * @param int $amount
     */
    public function __construct(int $payerId, int $recipientId, int $amount)
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
