<?php

namespace App\Services\Authorization;

use Illuminate\Support\Facades\Http;

class AuthorizationService
{
    /**
     * Authorize the application by making a GET request.
     *
     * This method sends a GET request to the authorization endpoint of the
     * external service to obtain the authorization status.
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function authorize()
    {
        return Http::get('https://util.devi.tools/api/v2/authorize');
    }
}
