<?php

namespace App\Services\Transfer;

use App\Data\TransferRequestData;
use App\Events\TransactionCreated;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Authorization\AuthorizationService;

class TransferOrchestrator
{
    protected AuthorizationService $authService;
    protected PayerTypeValidator $payerTypeValidator;
    protected BalanceValidator $balanceValidator;
    protected TransferProcessor $transferProcessor;
    protected RecipientResolver $recipientResolver;
    protected TransferRepositoryInterface $transferRepository;

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
        PayerTypeValidator $payerTypeValidator,
        BalanceValidator $balanceValidator,
        TransferProcessor $transferProcessor,
        RecipientResolver $recipientResolver,
        TransferRepositoryInterface $transferRepository,
    ) {
        $this->authService = $authService;
        $this->payerTypeValidator = $payerTypeValidator;
        $this->balanceValidator = $balanceValidator;
        $this->transferProcessor = $transferProcessor;
        $this->recipientResolver = $recipientResolver;
        $this->transferRepository = $transferRepository;
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
     * @throws \App\Exceptions\MerchantCannotTransferException
     */
    public function orchestrate(TransferRequestData $data): void
    {
        $payer = $this->transferRepository->getUserDataById($data->payerId);
        $recipient = $this->recipientResolver->resolve($data->recipientId);

        $this->payerTypeValidator->validate($payer);
        $this->balanceValidator->validate($payer, $data->amount);
        $this->authService->ensureAuthorized();
        $this->transferProcessor->process($payer, $recipient, $data->amount);
        event(new TransactionCreated($payer->id, $recipient->id, $data->amount));
    }
}
