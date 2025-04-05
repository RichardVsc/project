<?php

namespace Tests\Unit\Notification;

use App\Jobs\Notification\SendNotificationJob;
use App\Repositories\Statement\StatementRepositoryInterface;
use App\Services\Notification\NotificationService;
use App\Services\Statement\StatementService;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    protected $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationService = new NotificationService();
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testSendDispatchesNotificationJob()
    {
        Queue::fake();

        $userId = 1;
        $message = 'Transferência recebida com sucesso.';

        $this->notificationService->send($userId, $message);

        Queue::assertPushed(SendNotificationJob::class, function ($job) use ($userId, $message) {
            return $job->userId === $userId && $job->message === $message;
        });
    }
}
