<?php

namespace App\Services\Transfer;

use App\Exceptions\InsufficientFundsException;
use App\Models\User;

class BalanceValidator
{
    /**
     * Check if the payer has enough balance for the transfer.
     *
     * @param User $payer
     * @param float $amount
     * @return void
     * @throws InsufficientFundsException
     */
    public function validate(User $payer, float $amount)
    {
        if ($payer->balance < $amount) {
            throw new InsufficientFundsException('Saldo insuficiente para realizar a transferência.', 400);
        }
    }
}
