<?php

namespace App\Services\Authorization;

use App\Exceptions\Authorization\AuthorizationDeniedException;
use App\Exceptions\Authorization\AuthorizationServiceException;
use Illuminate\Support\Facades\Http;

class AuthorizationService
{
    /**
     * Authorize the application by making a GET request.
     *
     * This method sends a GET request to the authorization endpoint of the
     * external service to obtain the authorization status.
     *
     *
     * @return void
     * @throws AuthorizationDeniedException
     * @throws AuthorizationServiceException
     */
    public function ensureAuthorized(): void
    {
        try {
            $response = Http::get('https://util.devi.tools/api/v2/authorize');

            if (!$response->json('data.authorization')) {
                throw new AuthorizationDeniedException('Transação não autorizada pelo serviço externo.', 502);
            }
        } catch (AuthorizationDeniedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AuthorizationServiceException('Erro ao consultar serviço autorizador.', 500, $e);
        }
    }
}
