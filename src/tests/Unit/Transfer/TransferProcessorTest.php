<?php

namespace Tests\Unit\Transfer;

use App\Data\UserData;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\Transfer\TransferProcessException;
use App\Mappers\UserDataMapper;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Transfer\TransferProcessor;
use App\Validators\Transfer\BalanceValidator;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Tests\TestCase;

class TransferProcessorTest extends TestCase
{
    protected $databaseMock;
    protected $connectionMock;
    protected $transferRepositoryMock;
    protected $transferProcessor;
    protected $balanceValidatorMock;
    protected $userDataMapperMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseMock = Mockery::mock(DatabaseManager::class);
        $this->connectionMock = Mockery::mock();
        $this->transferRepositoryMock = Mockery::mock(TransferRepositoryInterface::class);
        $this->balanceValidatorMock = Mockery::mock(BalanceValidator::class);
        $this->userDataMapperMock = Mockery::mock(UserDataMapper::class);

        $this->databaseMock
            ->shouldReceive('connection')
            ->andReturn($this->connectionMock);

        $this->transferProcessor = new TransferProcessor($this->databaseMock, $this->transferRepositoryMock, $this->balanceValidatorMock, $this->userDataMapperMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testProcessSucceedsWhenBalanceIsSufficient()
    {
        $payerDto = new UserData(id: 1, user_type: 'common', balance: 100);
        $amount = 50.0;

        $payer = Mockery::mock(User::class)->makePartial();
        $payer->id = 1;
        $payer->balance = 100;

        $recipient = Mockery::mock(User::class)->makePartial();
        $recipient->id = 2;
        $recipient->balance = 10;

        $this->connectionMock->shouldReceive('beginTransaction')->once();
        $this->connectionMock->shouldReceive('commit')->once();

        $this->transferRepositoryMock
            ->shouldReceive('findAndLockUserById')
            ->with($payerDto->id)
            ->andReturn($payer);

        $this->transferRepositoryMock
            ->shouldReceive('findAndLockUserById')
            ->with($recipient->id)
            ->andReturn($recipient);

        $this->transferRepositoryMock
            ->shouldReceive('debitUser')
            ->once()
            ->with($payer, $amount);

        $this->transferRepositoryMock
            ->shouldReceive('creditUser')
            ->once()
            ->with($recipient, $amount);

        $this->transferRepositoryMock
            ->shouldReceive('createTransfer')
            ->once()
            ->with([
                'payer' => $payer->id,
                'payee' => $recipient->id,
                'value' => $amount,
            ]);

        $this->userDataMapperMock
            ->shouldReceive('fromModel')
            ->with($payer)
            ->andReturn($payerDto);

        $this->balanceValidatorMock
            ->shouldReceive('validate')
            ->once()
            ->with($payerDto, $amount);


        $this->transferProcessor->process($payerDto, $recipient, $amount);

        $this->assertTrue(true);
    }

    public function testProcessThrowsInsufficientFundsException()
    {
        $payerDto = new UserData(id: 1, user_type: 'common', balance: 10);
        $amount = 50.0;

        $payer = Mockery::mock(User::class)->makePartial();
        $payer->id = 1;
        $payer->balance = 10;

        $recipient = Mockery::mock(User::class)->makePartial();
        $recipient->id = 2;
        $recipient->balance = 100;

        $this->connectionMock->shouldReceive('beginTransaction')->once();
        $this->connectionMock->shouldReceive('rollBack')->once();

        $this->transferRepositoryMock
            ->shouldReceive('findAndLockUserById')
            ->with($payerDto->id)
            ->andReturn($payer);

        $this->transferRepositoryMock
            ->shouldReceive('findAndLockUserById')
            ->with($recipient->id)
            ->andReturn($recipient);

        $this->userDataMapperMock
            ->shouldReceive('fromModel')
            ->with($payer)
            ->andReturn($payerDto);

        $this->balanceValidatorMock
            ->shouldReceive('validate')
            ->with($payerDto, $amount)
            ->once()
            ->andThrow(new InsufficientFundsException('Saldo insuficiente.'));

        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Saldo insuficiente.');

        $this->transferProcessor->process($payerDto, $recipient, $amount);
    }

    public function testProcessThrowsTransferProcessExceptionOnGenericError()
    {
        $payerDto = new UserData(id: 1, user_type: 'common', balance: 100);
        $amount = 50.0;

        $recipient = Mockery::mock(User::class)->makePartial();
        $recipient->id = 2;

        $this->connectionMock->shouldReceive('beginTransaction')->once();
        $this->connectionMock->shouldReceive('rollBack')->once();

        $this->transferRepositoryMock
            ->shouldReceive('findAndLockUserById')
            ->andThrow(new \Exception('DB error'));

        $this->expectException(TransferProcessException::class);

        $this->transferProcessor->process($payerDto, $recipient, $amount);
    }
}
