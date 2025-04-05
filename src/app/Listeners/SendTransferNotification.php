<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Services\Notification\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTransferNotification implements ShouldQueue
{
    protected NotificationDispatcher $notificationDispatcher;
    /**
     * Create the event listener.
     */
    public function __construct(NotificationDispatcher $notificationDispatcher)
    {
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionCreated $event): void
    {
        $this->notificationDispatcher->dispatch($event->recipient->id);
    }
}
