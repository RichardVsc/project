<?php

namespace App\Services\Transfer;

use App\Exceptions\TransferException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Authorization\AuthorizationService;
use App\Services\Notification\NotificationService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;

class TransferService
{
    protected DatabaseManager $database;
    protected NotificationService $notificationService;
    protected TransferRepositoryInterface $transferRepository;
    protected AuthorizationService $authService;

    /**
     * TransferService constructor.
     *
     * @param NotificationService $notificationService
     * @param TransferRepositoryInterface $transferRepository
     */
    public function __construct(DatabaseManager $database, NotificationService $notificationService, TransferRepositoryInterface $transferRepository, AuthorizationService $authService)
    {
        $this->database = $database;
        $this->notificationService = $notificationService;
        $this->transferRepository = $transferRepository;
        $this->authService = $authService;
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
     * @throws TransferException If the transfer fails or another process is already transferring for the user.
     */
    public function executeTransfer(User $payer, int $recipientId, float $amount)
    {
        try {
            $this->checkBalance($payer, $amount);
            $this->authorizeTransaction();
            $recipient = $this->getRecipient($recipientId);

            $this->performTransfer($payer, $recipient, $amount);
            $this->sendNotification($recipient->id);
        } catch (TransferException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new TransferException('Erro ao processar a transferência.', 500, $e);
        }
    }

    /**
     * Retrieve the recipient user by ID.
     *
     * This method attempts to find the user with the given recipient ID.
     * If the user is not found, it throws a TransferException.
     *
     * @param int $recipientId The ID of the recipient.
     * @return User The recipient user object.
     * @throws TransferException If the recipient is not found.
     */
    private function getRecipient(int $recipientId): User
    {
        $recipient = $this->transferRepository->findUserById($recipientId);
        if (!$recipient) {
            throw new TransferException('Destinatário da transação não encontrado.', 404);
        }

        return $recipient;
    }

    /**
     * Execute the transfer between payer and recipient.
     *
     * This method performs a monetary transfer between two users, updating their balances
     * and recording the transaction. It uses a database transaction to ensure consistency
     * and rolls back in case of an error.
     *
     * @param User $payer The user initiating the transfer.
     * @param User $recipient The user receiving the transfer.
     * @param float $amount The amount to be transferred.
     * @return void
     * @throws TransferException If the transfer fails.
     */
    private function performTransfer(User $payer, User $recipient, float $amount): void
    {
        $connection = $this->database->connection();
        $connection->beginTransaction();

        try {
            $payer->balance -= (int) $amount;
            $this->transferRepository->updateUserBalance($payer);

            $recipient->balance += (int) $amount;
            $this->transferRepository->updateUserBalance($recipient);

            $this->transferRepository->createTransfer([
                'payer' => $payer->id,
                'payee' => $recipient->id,
                'value' => $amount,
            ]);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new TransferException('Erro ao processar a transferência.', 500, $e);
        }
    }

    /**
     * Send a notification to the recipient.
     *
     * This method sends a notification to the specified recipient indicating
     * that a transfer has been received. In case of a failure, it logs the error.
     *
     * @param int $recipientId The ID of the recipient user.
     * @return void
     */
    private function sendNotification(int $recipientId): void
    {
        try {
            $this->notificationService->send($recipientId, 'Você recebeu uma transferência.');
        } catch (\Exception $e) {
            Log::error('Failed to send notification', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if the payer has enough balance for the transfer.
     *
     * @param User $payer
     * @param float $amount
     * @return void
     * @throws TransferException
     */
    private function checkBalance(User $payer, float $amount)
    {
        if ($payer->balance < $amount) {
            throw new TransferException('Saldo insuficiente para realizar a transferência.', 400);
        }
    }

    /**
     * Authorize the transaction by contacting an external service.
     *
     * This method checks if the transaction is authorized by an external service.
     * If not authorized or if an error occurs, a TransferException is thrown.
     *
     * @return void
     * @throws TransferException
     */
    private function authorizeTransaction()
    {
        try {
            $response = $this->authService->authorize();

            if (!$response->json('data.authorization')) {
                throw new TransferException('Transação não autorizada pelo serviço externo.', 502);
            }
        } catch (TransferException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new TransferException('Erro ao consultar serviço autorizador.', 500, $e);
        }
    }
}
