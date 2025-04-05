<?php

namespace Tests\Unit\Transfer;

use App\Data\TransferRequestData;
use App\Data\UserData;
use App\Events\TransactionCreated;
use App\Exceptions\AuthorizationDeniedException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\RecipientNotFoundException;
use App\Exceptions\TransferProcessException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Authorization\AuthorizationService;
use App\Services\Transfer\BalanceValidator;
use App\Services\Transfer\RecipientResolver;
use App\Services\Transfer\TransferOrchestrator;
use App\Services\Transfer\TransferProcessor;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class TransferOrchestratorTest extends TestCase
{
    protected $authServiceMock;
    protected $balanceValidatorMock;
    protected $transferProcessorMock;
    protected $recipientResolverMock;
    protected $transferRepositoryMock;
    protected $transferOrchestrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authServiceMock = Mockery::mock(AuthorizationService::class);
        $this->balanceValidatorMock = Mockery::mock(BalanceValidator::class);
        $this->transferProcessorMock = Mockery::mock(TransferProcessor::class);
        $this->recipientResolverMock = Mockery::mock(RecipientResolver::class);
        $this->transferRepositoryMock = Mockery::mock(TransferRepositoryInterface::class);
        $this->transferOrchestrator = new TransferOrchestrator(
            $this->authServiceMock,
            $this->balanceValidatorMock,
            $this->transferProcessorMock,
            $this->recipientResolverMock,
            $this->transferRepositoryMock
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testOrchestrateRunsSuccessfully()
    {
        Event::fake();

        $data = new TransferRequestData(payerId: 1, recipientId: 2, amount: 100.0);
        $payer = new UserData(id: 1, balance: 200.0);
        $recipient = Mockery::mock(User::class)->makePartial();
        $recipient->id = 2;

        $this->transferRepositoryMock
            ->shouldReceive('getUserDataById')
            ->with($data->payerId)
            ->once()
            ->andReturn($payer);

        $this->recipientResolverMock
            ->shouldReceive('resolve')
            ->with($data->recipientId)
            ->once()
            ->andReturn($recipient);

        $this->balanceValidatorMock
            ->shouldReceive('validate')
            ->with($payer, $data->amount)
            ->once();

        $this->authServiceMock
            ->shouldReceive('ensureAuthorized')
            ->once();

        $this->transferProcessorMock
            ->shouldReceive('process')
            ->with($payer, $recipient, $data->amount)
            ->once();

        $this->transferOrchestrator->orchestrate($data);

        Event::assertDispatched(TransactionCreated::class, function ($event) use ($data) {
            return $event->payerId === $data->payerId &&
                $event->recipientId === $data->recipientId &&
                $event->amount === $data->amount;
        });
    }

    public function testOrchestrateThrowsInsufficientFunds()
    {
        $this->expectException(InsufficientFundsException::class);

        $data = new TransferRequestData(payerId: 1, recipientId: 2, amount: 150);
        $payer = new UserData(id: 1, balance: 100);
        $recipient = Mockery::mock(User::class)->makePartial();
        $recipient->id = 2;

        $this->transferRepositoryMock
            ->shouldReceive('getUserDataById')
            ->andReturn($payer);

        $this->recipientResolverMock
            ->shouldReceive('resolve')
            ->andReturn($recipient);

        $this->balanceValidatorMock
            ->shouldReceive('validate')
            ->andThrow(new InsufficientFundsException('Saldo insuficiente'));

        $this->transferOrchestrator->orchestrate($data);
    }

    public function testOrchestrateThrowsAuthorizationDenied()
    {
        $this->expectException(AuthorizationDeniedException::class);

        $data = new TransferRequestData(payerId: 1, recipientId: 2, amount: 100);
        $payer = new UserData(id: 1, balance: 200);
        $recipient = Mockery::mock(User::class)->makePartial();
        $recipient->id = 2;

        $this->transferRepositoryMock
            ->shouldReceive('getUserDataById')
            ->andReturn($payer);

        $this->recipientResolverMock
            ->shouldReceive('resolve')
            ->andReturn($recipient);

        $this->balanceValidatorMock
            ->shouldReceive('validate');

        $this->authServiceMock
            ->shouldReceive('ensureAuthorized')
            ->andThrow(new AuthorizationDeniedException('Negado'));

        $this->transferOrchestrator->orchestrate($data);
    }

    public function testOrchestrateThrowsRecipientNotFound()
    {
        $this->expectException(\App\Exceptions\RecipientNotFoundException::class);

        $data = new TransferRequestData(payerId: 1, recipientId: 999, amount: 50);
        $payer = new UserData(id: 1, balance: 500);

        $this->transferRepositoryMock
            ->shouldReceive('getUserDataById')
            ->with($data->payerId)
            ->andReturn($payer);

        $this->recipientResolverMock
            ->shouldReceive('resolve')
            ->with($data->recipientId)
            ->andThrow(new RecipientNotFoundException('Usuário não encontrado'));

        $this->transferOrchestrator->orchestrate($data);
    }

    public function testOrchestrateThrowsTransferProcessException()
    {
        $this->expectException(\App\Exceptions\TransferProcessException::class);

        $data = new TransferRequestData(payerId: 1, recipientId: 2, amount: 100);
        $payer = new UserData(id: 1, balance: 200);
        $recipient = Mockery::mock(User::class)->makePartial();
        $recipient->id = 2;

        $this->transferRepositoryMock
            ->shouldReceive('getUserDataById')
            ->andReturn($payer);

        $this->recipientResolverMock
            ->shouldReceive('resolve')
            ->andReturn($recipient);

        $this->balanceValidatorMock
            ->shouldReceive('validate');

        $this->authServiceMock
            ->shouldReceive('ensureAuthorized');

        $this->transferProcessorMock
            ->shouldReceive('process')
            ->with($payer, $recipient, $data->amount)
            ->andThrow(new TransferProcessException('Erro ao processar'));

        $this->transferOrchestrator->orchestrate($data);
    }
}
