<?php

namespace App\Services\Transfer;

use App\Exceptions\AuthorizationDeniedException;
use App\Exceptions\AuthorizationServiceException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\RecipientNotFoundException;
use App\Exceptions\TransferException;
use App\Exceptions\TransferProcessException;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use App\Services\Notification\NotificationDispatcher;

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
     * @param User $payer The user initiating the transfer.
     * @param int $recipientId The ID of the recipient user.
     * @param float $amount The amount to be transferred.
     * @return void
     * @throws InsufficientFundsException If the user has insufficient funds.
     * @throws AuthorizationDeniedException If the third party API doesnt authorize the request.
     * @throws AuthorizationServiceException If the third party API could not be reached.
     * @throws RecipientNotFoundException If the payee cannot be found.
     * @throws TransferProcessException If the transfer process fails.
     * @throws TransferException If the transfer fails.
     */
    public function transfer(User $payer, int $recipientId, float $amount)
    {
        try {
            $this->transferOrchestrator->orchestrate($payer, $recipientId, $amount);
        } catch (
            InsufficientFundsException |
            RecipientNotFoundException |
            TransferProcessException |
            AuthorizationDeniedException |
            AuthorizationServiceException $e
        ) {
            throw $e;
        } catch (\Exception $e) {
            throw new TransferException('Erro ao processar a transferência.', 500, $e);
        }
    }
}
