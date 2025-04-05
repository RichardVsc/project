<?php

namespace Tests\Unit\Transfer;

use App\Exceptions\RecipientNotFoundException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Transfer\RecipientResolver;
use Mockery;
use Tests\TestCase;

class RecipientResolverTest extends TestCase
{
    protected $recipientResolver;
    protected $transferRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transferRepositoryMock = Mockery::mock(TransferRepositoryInterface::class);
        $this->recipientResolver = new RecipientResolver($this->transferRepositoryMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testResolveReturnsUserWhenFound()
    {
        $userId = 2;
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = $userId;
        $user->name = 'Teste';
        $user->email = 'teste@example.com';

        $this->transferRepositoryMock
            ->shouldReceive('findUserById')
            ->with($userId)
            ->once()
            ->andReturn($user);

        $result = $this->recipientResolver->resolve($userId);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userId, $result->id);
    }

    public function testResolveThrowsRecipientNotFoundExceptionWhenUserIsNotFound()
    {
        $this->expectException(RecipientNotFoundException::class);
        $this->expectExceptionMessage('Destinatário da transação não encontrado.');

        $userId = 999;

        $this->transferRepositoryMock
            ->shouldReceive('findUserById')
            ->with($userId)
            ->once()
            ->andReturn(null);

        $this->recipientResolver->resolve($userId);
    }
}
