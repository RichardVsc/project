<?php

namespace Tests\Unit\Notification;

use App\Events\TransactionCreated;
use App\Listeners\SendTransferNotification;
use App\Services\Notification\NotificationDispatcher;
use Mockery;
use Tests\TestCase;

class SendTransferNotificationTest extends TestCase
{
    protected $notificationDispatcherMock;
    protected SendTransferNotification $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationDispatcherMock = Mockery::mock(NotificationDispatcher::class);
        $this->listener = new SendTransferNotification($this->notificationDispatcherMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testHandleDispatchesNotification()
    {
        $recipientId = 7;
        $amount = 100.00;
        $event = new TransactionCreated(1, $recipientId, $amount);

        $this->notificationDispatcherMock
            ->shouldReceive('dispatch')
            ->with($recipientId)
            ->once();

        $this->listener->handle($event);

        $this->addToAssertionCount(1);
    }
}
