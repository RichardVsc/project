<?php

namespace App\Services\Transfer;

use App\Data\TransferRequestData;
use App\Events\TransactionCreated;
use App\Repositories\Transfer\TransferRepositoryInterface;

class TransferOrchestrator
{
    protected TransferValidator $transferValidator;
    protected TransferProcessor $transferProcessor;
    protected RecipientResolver $recipientResolver;
    protected TransferRepositoryInterface $transferRepository;

    /**
     * TransferService constructor.
     *
     * @param TransferValidator $transferValidator
     * @param TransferProcessor $transferProcessor
     * @param RecipientResolver $recipientResolver
     * @param TransferRepositoryInterface $transferRepository
     */
    public function __construct(
        TransferValidator $transferValidator,
        TransferProcessor $transferProcessor,
        RecipientResolver $recipientResolver,
        TransferRepositoryInterface $transferRepository,
    ) {
        $this->transferValidator = $transferValidator;
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

        $this->transferValidator->validate($payer, $data->amount);
        $this->transferProcessor->process($payer, $recipient, $data->amount);
        event(new TransactionCreated($payer->id, $recipient->id, $data->amount));
    }
}
