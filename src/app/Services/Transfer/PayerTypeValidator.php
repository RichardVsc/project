<?php

namespace App\Services\Transfer;

use App\Data\UserData;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\MerchantCannotTransferException;

class PayerTypeValidator
{
    /**
     * Check if the payer has enough balance for the transfer.
     *
     * @param UserData $payer
     * @param float $amount
     * @return void
     * @throws InsufficientFundsException
     */
    public function validate(UserData $payer)
    {
        if ($payer->user_type === 'merchant') {
            throw new MerchantCannotTransferException('Lojistas não podem realizar transferências.');
        }
    }
}
