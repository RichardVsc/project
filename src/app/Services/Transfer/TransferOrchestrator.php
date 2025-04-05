<?php

namespace App\Services\Transfer;

use App\Data\TransferRequestData;
use App\Events\TransactionCreated;
use App\Services\Authorization\AuthorizationService;

class TransferOrchestrator
{
    protected AuthorizationService $authService;
    protected BalanceValidator $balanceValidator;
    protected TransferProcessor $transferProcessor;
    protected RecipientResolver $recipientResolver;

    /**
     * TransferService constructor.
     *
     * @param AuthorizationService $authService
     * @param BalanceValidator $balanceValidator
     * @param TransferProcessor $transferProcessor
     * @param RecipientResolver $recipientResolver
     */
    public function __construct(
        AuthorizationService $authService,
        BalanceValidator $balanceValidator,
        TransferProcessor $transferProcessor,
        RecipientResolver $recipientResolver,
    ) {
        $this->authService = $authService;
        $this->balanceValidator = $balanceValidator;
        $this->transferProcessor = $transferProcessor;
        $this->recipientResolver = $recipientResolver;
    }

    /**
     * Orchestrates the entire money transfer process between users.
     *
     * This method validates the payer's balance, performs third-party authorization,
     * resolves the recipient, executes the transfer, and dispatches a notification.
     *
     * Exceptions thrown during any step will propagate to the caller.
     *
     * @param TransferRequestData $data.
     *
     * @return void
     *
     * @throws \App\Exceptions\InsufficientFundsException
     * @throws \App\Exceptions\AuthorizationDeniedException
     * @throws \App\Exceptions\AuthorizationServiceException
     * @throws \App\Exceptions\RecipientNotFoundException
     * @throws \App\Exceptions\TransferProcessException
     */
    public function orchestrate(TransferRequestData $data): void
    {
        $payer = $this->recipientResolver->resolve($data->payerId);
        $recipient = $this->recipientResolver->resolve($data->recipientId);

        $this->balanceValidator->validate($payer, $data->amount);
        $this->authService->ensureAuthorized();
        $this->transferProcessor->process($payer, $recipient, $data->amount);
        event(new TransactionCreated($payer, $recipient, $data->amount));
    }
}
