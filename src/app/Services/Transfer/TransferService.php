<?php

namespace App\Services\Transfer;

use App\Data\TransferRequestData;
use App\Exceptions\AuthorizationDeniedException;
use App\Exceptions\AuthorizationServiceException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\MerchantCannotTransferException;
use App\Exceptions\RecipientNotFoundException;
use App\Exceptions\TransferException;
use App\Exceptions\TransferProcessException;

class TransferService
{
    protected TransferOrchestrator $transferOrchestrator;

    /**
     * TransferService constructor.
     *
     * @param TransferOrchestrator $transferOrchestrator
     */
    public function __construct(
        TransferOrchestrator $transferOrchestrator,
    ) {
        $this->transferOrchestrator = $transferOrchestrator;
    }

    /**
     * Execute a money transfer from the payer to the recipient.
     *
     * This method handles the complete transfer process, including balance validation,
     * authorization, transfer execution, and notification. It also ensures that the transfer
     * is performed in a thread-safe manner using a cache lock.
     *
     * @param TransferRequestData $data.
     * @return void
     * @throws MerchantCannotTransferException If the user is merchant.
     * @throws InsufficientFundsException If the user has insufficient funds.
     * @throws AuthorizationDeniedException If the third party API doesnt authorize the request.
     * @throws AuthorizationServiceException If the third party API could not be reached.
     * @throws RecipientNotFoundException If the payee cannot be found.
     * @throws TransferProcessException If the transfer process fails.
     */
    public function transfer(TransferRequestData $data): void
    {
        try {
            $this->transferOrchestrator->orchestrate($data);
        } catch (
            MerchantCannotTransferException |
            InsufficientFundsException |
            RecipientNotFoundException |
            TransferProcessException |
            AuthorizationDeniedException |
            AuthorizationServiceException $e
        ) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransferException('Erro ao processar a transferência.', 500, $e);
        }
    }
}
