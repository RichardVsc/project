<?php

namespace App\Data;

class UserData
{
    public function __construct(public readonly int $id, public readonly int $balance) {}

    public function hasSufficientFunds(int $amount): bool
    {
        return $this->balance >= $amount;
    }
}
