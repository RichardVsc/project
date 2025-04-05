<?php

namespace Tests\Feature\Transfer;

use App\Exceptions\RecipientNotFoundException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Transfer\RecipientResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RecipientResolverIntegrationTest extends TestCase
{
    protected RecipientResolver $recipientResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = app(TransferRepositoryInterface::class);
        $this->recipientResolver = new RecipientResolver($repository);
    }

    public function testResolveReturnsUserWhenFound(): void
    {
        $user = User::factory()->create();

        $resolvedUser = $this->recipientResolver->resolve($user->id);

        $this->assertInstanceOf(User::class, $resolvedUser);
        $this->assertEquals($user->id, $resolvedUser->id);
    }

    public function testResolveThrowsExceptionWhenUserNotFound(): void
    {
        $this->expectException(RecipientNotFoundException::class);
        $this->expectExceptionMessage('Destinatário da transação não encontrado.');

        $nonExistingUserId = 9999;
        $this->recipientResolver->resolve($nonExistingUserId);
    }
}
