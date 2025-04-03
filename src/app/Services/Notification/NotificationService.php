<?php

namespace App\Services\Notification;

use App\Jobs\Notification\SendNotificationJob;

class NotificationService
{
    public function send(int $userId, string $message)
    {
        SendNotificationJob::dispatch($userId, $message)->onQueue('default');
    }
}
