<?php

namespace App\Services\Transfer;

use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use App\Services\Notification\NotificationDispatcher;

class TransferOrchestrator
{
    protected AuthorizationService $authService;
    protected BalanceValidator $balanceValidator;
    protected TransferProcessor $transferProcessor;
    protected NotificationDispatcher $notificationDispatcher;
    protected RecipientResolver $recipientResolver;

    /**
     * TransferService constructor.
     *
     * @param AuthorizationService $authService
     * @param BalanceValidator $balanceValidator
     * @param TransferProcessor $transferProcessor
     * @param NotificationDispatcher $notificationDispatcher
     * @param RecipientResolver $recipientResolver
     */
    public function __construct(
        AuthorizationService $authService,
        BalanceValidator $balanceValidator,
        TransferProcessor $transferProcessor,
        NotificationDispatcher $notificationDispatcher,
        RecipientResolver $recipientResolver,
    ) {
        $this->authService = $authService;
        $this->balanceValidator = $balanceValidator;
        $this->transferProcessor = $transferProcessor;
        $this->notificationDispatcher = $notificationDispatcher;
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
     * @param User $payer The user initiating the transfer.
     * @param int $recipientId The ID of the recipient user.
     * @param float $amount The amount to be transferred.
     *
     * @return void
     *
     * @throws \App\Exceptions\InsufficientFundsException
     * @throws \App\Exceptions\AuthorizationDeniedException
     * @throws \App\Exceptions\AuthorizationServiceException
     * @throws \App\Exceptions\RecipientNotFoundException
     * @throws \App\Exceptions\TransferProcessException
     */
    public function orchestrate(User $payer, int $recipientId, float $amount)
    {
        $this->balanceValidator->validate($payer, $amount);
        $this->authService->ensureAuthorized();
        $recipient = $this->recipientResolver->resolve($recipientId);
        $this->transferProcessor->process($payer, $recipient, $amount);
        $this->notificationDispatcher->dispatch($recipient->id);
    }
}
