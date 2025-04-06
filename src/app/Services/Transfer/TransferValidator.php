<?php

namespace App\Services\Transfer;

use App\Data\UserData;
use App\Services\Authorization\AuthorizationService;
use App\Validators\Transfer\BalanceValidator;
use App\Validators\Transfer\PayerTypeValidator;

class TransferValidator
{
    protected AuthorizationService $authService;
    protected PayerTypeValidator $payerTypeValidator;
    protected BalanceValidator $balanceValidator;

    public function __construct(
        AuthorizationService $authService,
        PayerTypeValidator $payerTypeValidator,
        BalanceValidator $balanceValidator,
    ) {
        $this->authService = $authService;
        $this->payerTypeValidator = $payerTypeValidator;
        $this->balanceValidator = $balanceValidator;
    }

    /**
     * Validates the payer's type, balance, and third-party authorization.
     *
     * @throws \App\Exceptions\AuthorizationDeniedException
     * @throws \App\Exceptions\AuthorizationServiceException
     * @throws \App\Exceptions\InsufficientFundsException
     * @throws \App\Exceptions\MerchantCannotTransferException
     */
    public function validate(UserData $payer, float $amount): void
    {
        $this->payerTypeValidator->validate($payer);
        $this->balanceValidator->validate($payer, $amount);
        $this->authService->ensureAuthorized();
    }
}
