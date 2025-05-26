<?php

namespace Tests\Unit\Authorization;

use App\Exceptions\Authorization\AuthorizationDeniedException;
use App\Exceptions\Authorization\AuthorizationServiceException;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class AuthorizationServiceTest extends TestCase
{
    protected $authorizationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizationService = new AuthorizationService();
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testEnsureAuthorizedSucceedsWhenAuthorized()
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response([
                'data' => [
                    'authorization' => true,
                ],
            ], 200),
        ]);

        $this->authorizationService->ensureAuthorized();

        $this->assertTrue(true);
    }

    public function testEnsureAuthorizedThrowsAuthorizationDeniedException()
    {
        $this->expectException(AuthorizationDeniedException::class);

        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response([
                'data' => [
                    'authorization' => false,
                ],
            ], 200),
        ]);

        $this->authorizationService->ensureAuthorized();
    }

    public function testEnsureAuthorizedThrowsAuthorizationServiceExceptionOnNetworkError()
    {
        $this->expectException(AuthorizationServiceException::class);

        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => fn () => throw new \Exception('Falha na conexão'),
        ]);

        $this->authorizationService->ensureAuthorized();
    }
}
