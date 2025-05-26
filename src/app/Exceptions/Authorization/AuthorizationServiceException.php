<?php

namespace App\Exceptions\Authorization;

use Exception;

class AuthorizationServiceException extends Exception
{
    public function render()
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 500);
    }
}
