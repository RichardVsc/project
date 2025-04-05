<?php

namespace Tests\Unit\Statement;

use App\Repositories\Statement\StatementRepositoryInterface;
use App\Services\Statement\StatementService;
use Mockery;
use Tests\TestCase;

class StatementServiceTest extends TestCase
{
    protected $statementRepositoryMock;
    protected $statementService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statementRepositoryMock = Mockery::mock(StatementRepositoryInterface::class);
        $this->statementService = new StatementService($this->statementRepositoryMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetUserStatementReturnsTransactions()
    {
        $userId = 1;
        $transactions = [
            ['id' => 1, 'payer' => 1, 'payee' => 2, 'amount' => 50.00, 'created_at' => '2025-04-03 12:00:00'],
            ['id' => 2, 'payer' => 2, 'payee' => 1, 'amount' => 25.00, 'created_at' => '2025-04-02 08:30:00'],
        ];

        $this->statementRepositoryMock
            ->shouldReceive('getUserTransactions')
            ->with($userId)
            ->once()
            ->andReturn($transactions);

        $result = $this->statementService->getUserStatement($userId);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($transactions, $result);
    }
}
