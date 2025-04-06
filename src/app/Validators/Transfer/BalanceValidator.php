<?php

namespace App\Validators\Transfer;

use App\Data\UserData;
use App\Exceptions\InsufficientFundsException;

class BalanceValidator
{
    /**
     * Check if the payer has enough balance for the transfer.
     *
     * @param UserData $payer
     * @param float $amount
     * @return void
     * @throws InsufficientFundsException
     */
    public function validate(UserData $payer, float $amount)
    {
        if ($payer->balance < $amount) {
            throw new InsufficientFundsException('Saldo insuficiente para realizar a transferência.', 400);
        }
    }
}
