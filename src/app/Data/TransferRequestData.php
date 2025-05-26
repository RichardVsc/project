<?php

namespace App\Data;

class TransferRequestData
{
    public int $payerId;
    public int $recipientId;
    public int $amount;

    public function __construct(int $payerId, int $recipientId, int $amount)
    {
        $this->payerId = $payerId;
        $this->recipientId = $recipientId;
        $this->amount = $amount;
    }
}
