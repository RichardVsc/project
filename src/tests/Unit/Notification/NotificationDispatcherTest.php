<?php

namespace Tests\Unit\Notification;

use App\Jobs\Notification\SendNotificationJob;
use App\Services\Notification\NotificationDispatcher;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class NotificationDispatcherTest extends TestCase
{
    protected $notificationServiceMock;
    protected $notificationDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationServiceMock = Mockery::mock(NotificationService::class);
        $this->notificationDispatcher = new NotificationDispatcher($this->notificationServiceMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testDispatchCallsNotificationServiceSend()
    {
        $recipientId = 5;
        $expectedMessage = 'Você recebeu uma transferência.';

        $this->notificationServiceMock
            ->shouldReceive('send')
            ->with($recipientId, $expectedMessage)
            ->once();

        $this->notificationDispatcher->dispatch($recipientId);
        $this->addToAssertionCount(1);
    }

    public function testDispatchLogsErrorOnFailure()
    {
        Log::spy();

        $recipientId = 7;

        $this->notificationServiceMock
            ->shouldReceive('send')
            ->andThrow(new \Exception('Simulated failure'));

        $this->notificationDispatcher->dispatch($recipientId);

        Log::shouldHaveReceived('error')
            ->with('Failed to send notification', Mockery::on(function ($context) {
                return isset($context['error']) && $context['error'] === 'Simulated failure';
            }))
            ->once();
            
        $this->addToAssertionCount(1);
    }
}
