<?php

namespace Tests\Unit\Transfer;

use App\Data\TransferRequestData;
use App\Exceptions\AuthorizationDeniedException;
use App\Exceptions\AuthorizationServiceException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\RecipientNotFoundException;
use App\Exceptions\TransferException;
use App\Exceptions\TransferProcessException;
use App\Services\Transfer\TransferOrchestrator;
use App\Services\Transfer\TransferService;
use Mockery;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    protected $transferService;
    protected $transferOrchestratorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transferOrchestratorMock = Mockery::mock(TransferOrchestrator::class);
        $this->transferService = new TransferService($this->transferOrchestratorMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testTransferServiceCallsOrchestrateSuccessfully()
    {
        $data = Mockery::mock(TransferRequestData::class);

        $this->transferOrchestratorMock
            ->shouldReceive('orchestrate')
            ->with($data)
            ->once();

        $this->transferService->transfer($data);

        $this->assertTrue(true);
    }

    public function testTransferServiceThrowsKnownExceptions()
    {
        $exceptions = [
            AuthorizationDeniedException::class,
            AuthorizationServiceException::class,
            InsufficientFundsException::class,
            RecipientNotFoundException::class,
            TransferProcessException::class,
        ];

        foreach ($exceptions as $exception) {
            $this->expectException($exception);

            $data = Mockery::mock(TransferRequestData::class);

            $this->transferOrchestratorMock
                ->shouldReceive('orchestrate')
                ->with($data)
                ->once()
                ->andThrow(new $exception('Erro'));

            $this->transferService->transfer($data);
        }
    }

    public function testTransferServiceThrowsGenericTransferExceptionForUnexpectedError()
    {
        $this->expectException(TransferException::class);

        $data = Mockery::mock(TransferRequestData::class);

        $this->transferOrchestratorMock
            ->shouldReceive('orchestrate')
            ->with($data)
            ->once()
            ->andThrow(new \RuntimeException('Erro inesperado'));

        $this->transferService->transfer($data);
    }
}
