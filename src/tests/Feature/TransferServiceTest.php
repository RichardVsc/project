<?php

namespace Tests\Feature;

use App\Exceptions\AuthorizationDeniedException;
use App\Exceptions\AuthorizationServiceException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\RecipientNotFoundException;
use App\Exceptions\TransferProcessException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepository;
use App\Services\Authorization\AuthorizationService;
use App\Services\Notification\NotificationService;
use App\Services\Transfer\TransferService;
use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    protected $dbMock;
    protected $notificationServiceMock;
    protected $transferRepositoryMock;
    protected $authServiceMock;
    protected $transferService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbMock = Mockery::mock(DatabaseManager::class);
        $this->notificationServiceMock = Mockery::mock(NotificationService::class);
        $this->transferRepositoryMock = Mockery::mock(TransferRepository::class);
        $this->authServiceMock = Mockery::mock(AuthorizationService::class);

        $this->transferService = new TransferService($this->dbMock, $this->notificationServiceMock, $this->transferRepositoryMock, $this->authServiceMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testExecuteTransferThrowsExceptionWhenInsufficientBalance()
    {
        $payer = new User(['id' => 1, 'balance' => 50.00]);
        $recipientId = 2;
        $amount = 100.00;

        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Saldo insuficiente para realizar a transferência.');

        $this->transferService->executeTransfer($payer, $recipientId, $amount);
    }

    public function testExecuteTransferThrowsExceptionWhenAuthorizationFails()
    {
        $payer = new User(['id' => 1, 'balance' => 150.00]);
        $recipientId = 2;
        $amount = 50.00;

        $this->authServiceMock->shouldReceive('authorize')
            ->once()
            ->andReturn(new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['authorization' => false]]))
            ));

        $this->expectException(AuthorizationDeniedException::class);
        $this->expectExceptionMessage('Transação não autorizada pelo serviço externo.');

        $this->transferService->executeTransfer($payer, $recipientId, $amount);
    }

    public function testExecuteTransferThrowsExceptionWhenHttpRequestFails()
    {
        $payer = new User(['id' => 1, 'balance' => 150.00]);
        $recipientId = 2;
        $amount = 50.00;

        $this->authServiceMock->shouldReceive('authorize')
            ->once()
            ->andThrow(new Exception('HTTP request failed'));

        $this->expectException(AuthorizationServiceException::class);
        $this->expectExceptionMessage('Erro ao consultar serviço autorizador.');

        $this->transferService->executeTransfer($payer, $recipientId, $amount);
    }

    public function testExecuteTransferThrowsExceptionWhenAuthorizationResponseInvalid()
    {
        $payer = new User(['id' => 1, 'balance' => 150.00]);
        $recipientId = 2;
        $amount = 50.00;

        $this->authServiceMock->shouldReceive('authorize')
            ->once()
            ->andReturn(new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['unexpected' => 'response']))
            ));

        $this->expectException(AuthorizationDeniedException::class);
        $this->expectExceptionMessage('Transação não autorizada pelo serviço externo.');

        $this->transferService->executeTransfer($payer, $recipientId, $amount);
    }

    public function testExecuteTransferThrowsExceptionWhenRecipientNotFound()
    {
        $payer = new User(['id' => 1, 'balance' => 150.00]);
        $recipientId = 999;
        $amount = 50.00;

        $this->authServiceMock->shouldReceive('authorize')
            ->once()
            ->andReturn(new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['authorization' => true]]))
            ));

        $this->transferRepositoryMock
            ->shouldReceive('findUserById')
            ->with($recipientId)
            ->andReturn(null);

        $this->expectException(RecipientNotFoundException::class);
        $this->expectExceptionMessage('Destinatário da transação não encontrado.');

        $this->transferService->executeTransfer($payer, $recipientId, $amount);
    }

    public function testExecuteTransferSucceedsWhenRecipientExists()
    {
        $payer = new User(['balance' => 200.00]);
        $payer->id = 1;
        $recipient = new User(['balance' => 100.00]);
        $recipient->id = 2;
        $amount = 50.00;

        $this->authServiceMock->shouldReceive('authorize')
            ->once()
            ->andReturn(new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['authorization' => true]]))
            ));

        $connectionMock = Mockery::mock();
        $connectionMock->shouldReceive('beginTransaction')->once();
        $connectionMock->shouldReceive('commit')->once();
        $connectionMock->shouldReceive('rollBack')->never();

        $this->dbMock
            ->shouldReceive('connection')
            ->andReturn($connectionMock);

        $this->transferRepositoryMock
            ->shouldReceive('findUserById')
            ->andReturn($recipient);

        $this->transferRepositoryMock
            ->shouldReceive('updateUserBalance')
            ->twice();

        $this->transferRepositoryMock
            ->shouldReceive('createTransfer')
            ->once();

        $this->notificationServiceMock
            ->shouldReceive('send')
            ->with($recipient->id, 'Você recebeu uma transferência.')
            ->once();

        $this->transferService->executeTransfer($payer, $recipient->id, $amount);
        $this->assertTrue(true, 'Transfer executed successfully.');
    }

    public function testExecuteTransferLogsErrorWhenNotificationFails()
    {
        $payer = new User(['balance' => 200.00]);
        $payer->id = 1;
        $recipient = new User(['balance' => 100.00]);
        $recipient->id = 2;
        $amount = 50.00;

        $this->authServiceMock->shouldReceive('authorize')
            ->once()
            ->andReturn(new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['authorization' => true]]))
            ));

        $connectionMock = Mockery::mock();
        $connectionMock->shouldReceive('beginTransaction')->once();
        $connectionMock->shouldReceive('commit')->once();
        $connectionMock->shouldReceive('rollBack')->never();

        $this->dbMock
            ->shouldReceive('connection')
            ->andReturn($connectionMock);

        $this->transferRepositoryMock
            ->shouldReceive('findUserById')
            ->andReturn($recipient);

        $this->transferRepositoryMock
            ->shouldReceive('updateUserBalance')
            ->twice();

        $this->transferRepositoryMock
            ->shouldReceive('createTransfer')
            ->once();

        $this->notificationServiceMock
            ->shouldReceive('send')
            ->with($recipient->id, 'Você recebeu uma transferência.')
            ->andThrow(new Exception('Notification service failed'));

        Log::shouldReceive('error')
            ->once()
            ->with('Failed to send notification', Mockery::type('array'));

        $this->transferService->executeTransfer($payer, $recipient->id, $amount);
        $this->assertTrue(true, 'Transfer executed with notification failure logged.');
    }

    public function testExecuteTransferThrowsExceptionWhenDatabaseFails()
    {
        $payer = new User(['balance' => 200.00]);
        $payer->id = 1;
        $recipient = new User(['balance' => 100.00]);
        $recipient->id = 2;
        $amount = 50.00;

        $this->authServiceMock->shouldReceive('authorize')
            ->once()
            ->andReturn(new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['authorization' => true]]))
            ));

        $connectionMock = Mockery::mock();
        $connectionMock->shouldReceive('beginTransaction')->once();
        $connectionMock->shouldReceive('rollBack')->once();

        $this->dbMock
            ->shouldReceive('connection')
            ->andReturn($connectionMock);

        $this->transferRepositoryMock
            ->shouldReceive('findUserById')
            ->andReturn($recipient);

        $this->transferRepositoryMock
            ->shouldReceive('updateUserBalance')
            ->andThrow(new Exception('Database error during update'));

        $this->expectException(TransferProcessException::class);
        $this->expectExceptionMessage('Erro ao processar a transferência.');

        $this->transferService->executeTransfer($payer, $recipient->id, $amount);
    }
}
