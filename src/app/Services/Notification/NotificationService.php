<?php

namespace App\Services\Notification;

use App\Jobs\Notification\SendNotificationJob;

class NotificationService
{
    /**
     * Send a notification to a user.
     *
     * This method dispatches a job to send a notification to a user, passing the
     * user ID and message to the `SendNotificationJob`. The job will be placed
     * on the 'default' queue.
     *
     * @param int $userId
     * @param string $message
     * @return void
     */
    public function send(int $userId, string $message)
    {
        SendNotificationJob::dispatch($userId, $message);
    }
}
