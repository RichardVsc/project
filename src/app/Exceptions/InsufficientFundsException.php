<?php

namespace App\Exceptions;

use Exception;

class InsufficientFundsException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 400);
    }
}
