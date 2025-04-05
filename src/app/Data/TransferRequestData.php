<?php

namespace App\Data;

class TransferRequestData
{
    public int $payerId;
    public int $recipientId;
    public float $amount;

    public function __construct(int $payerId, int $recipientId, float $amount)
    {
        $this->payerId = $payerId;
        $this->recipientId = $recipientId;
        $this->amount = $amount;
    }
}
