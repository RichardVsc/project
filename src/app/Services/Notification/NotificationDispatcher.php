<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Log;

class NotificationDispatcher
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send a notification to the recipient.
     *
     * This method sends a notification to the specified recipient indicating
     * that a transfer has been received. In case of a failure, it logs the error.
     *
     * @param int $recipientId The ID of the recipient user.
     * @return void
     */
    public function dispatch(int $recipientId): void
    {
        try {
            $this->notificationService->send($recipientId, 'Você recebeu uma transferência.');
        } catch (\Exception $e) {
            Log::error('Failed to send notification', ['error' => $e->getMessage()]);
        }
    }
}
