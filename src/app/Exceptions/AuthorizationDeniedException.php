<?php

namespace App\Exceptions;

use Exception;

class AuthorizationDeniedException extends Exception
{
    public function render()
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 502);
    }
}
