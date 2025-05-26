<?php

namespace App\Exceptions\Transfer;

use Exception;

class TransferProcessException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 500);
    }
}
