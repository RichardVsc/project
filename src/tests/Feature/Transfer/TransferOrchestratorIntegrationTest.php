<?php

namespace Tests\Feature\Transfer;

use App\Data\TransferRequestData;
use App\Events\TransactionCreated;
use App\Exceptions\AuthorizationDeniedException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\RecipientNotFoundException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepository;
use App\Services\Authorization\AuthorizationService;
use App\Services\Transfer\BalanceValidator;
use App\Services\Transfer\RecipientResolver;
use App\Services\Transfer\TransferOrchestrator;
use App\Services\Transfer\TransferProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class TransferOrchestratorIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected TransferOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = new TransferRepository();
        $database = app('db');
        $authService = new AuthorizationService();
        $balanceValidator = new BalanceValidator();
        $transferProcessor = new TransferProcessor($database, $repository);
        $recipientResolver = new RecipientResolver($repository);

        $this->orchestrator = new TransferOrchestrator(
            $authService,
            $balanceValidator,
            $transferProcessor,
            $recipientResolver,
            $repository,
        );
    }

    public function testOrchestrateSuccessfullyTransfersAmount(): void
    {
        Event::fake([
            TransactionCreated::class,
        ]);

        $authService = \Mockery::mock(AuthorizationService::class);
        $authService->shouldReceive('ensureAuthorized')->once()->andReturnTrue();

        $this->app->instance(AuthorizationService::class, $authService);

        $repository = new TransferRepository();
        $database = app('db');
        $balanceValidator = new BalanceValidator();
        $transferProcessor = new TransferProcessor($database, $repository);
        $recipientResolver = new RecipientResolver($repository);

        $orchestrator = new TransferOrchestrator(
            $authService,
            $balanceValidator,
            $transferProcessor,
            $recipientResolver,
            $repository
        );

        $payer = User::factory()->create(['balance' => 1000]);
        $recipient = User::factory()->create(['balance' => 500]);

        $data = new TransferRequestData(
            payerId: $payer->id,
            recipientId: $recipient->id,
            amount: 300
        );

        $orchestrator->orchestrate($data);

        $this->assertDatabaseHas('users', [
            'id' => $payer->id,
            'balance' => 700,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $recipient->id,
            'balance' => 800,
        ]);

        $this->assertDatabaseHas('transfers', [
            'payer' => $payer->id,
            'payee' => $recipient->id,
            'value' => 300,
        ]);

        Event::assertDispatched(TransactionCreated::class, function ($event) use ($payer, $recipient) {
            return $event->payerId === $payer->id &&
                $event->recipientId === $recipient->id &&
                $event->amount == 300;
        });
    }

    public function testOrchestrateThrowsExceptionOnInsufficientFunds(): void
    {
        $this->expectException(InsufficientFundsException::class);

        $payer = User::factory()->create(['balance' => 100]);
        $recipient = User::factory()->create(['balance' => 200]);

        $data = new TransferRequestData(
            payerId: $payer->id,
            recipientId: $recipient->id,
            amount: 300
        );

        $this->orchestrator->orchestrate($data);
    }

    public function testOrchestrateThrowsExceptionIfRecipientNotFound(): void
    {
        $this->expectException(RecipientNotFoundException::class);

        $payer = User::factory()->create(['balance' => 1000]);

        $data = new TransferRequestData(
            payerId: $payer->id,
            recipientId: 99999,
            amount: 100
        );

        $this->orchestrator->orchestrate($data);
    }

    public function testOrchestrateThrowsExceptionWhenAuthorizationFails(): void
    {
        $this->expectException(AuthorizationDeniedException::class);

        $payer = User::factory()->create(['balance' => 1000]);
        $recipient = User::factory()->create(['balance' => 100]);

        $mock = Mockery::mock(AuthorizationService::class);
        $mock->shouldReceive('ensureAuthorized')->once()
            ->andThrow(new AuthorizationDeniedException('Negado', 502));
        $this->app->instance(AuthorizationService::class, $mock);

        $repository = new TransferRepository();
        $database = app('db');
        $balanceValidator = new BalanceValidator();
        $transferProcessor = new TransferProcessor($database, $repository);
        $recipientResolver = new RecipientResolver($repository);

        $orchestrator = new TransferOrchestrator(
            $mock,
            $balanceValidator,
            $transferProcessor,
            $recipientResolver,
            $repository
        );

        $data = new TransferRequestData(
            payerId: $payer->id,
            recipientId: $recipient->id,
            amount: 100
        );

        $orchestrator->orchestrate($data);
    }
}
