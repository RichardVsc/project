<?php

namespace App\Services\Transfer;

use App\Data\UserData;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\TransferProcessException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use Illuminate\Database\DatabaseManager;

class TransferProcessor
{
    protected DatabaseManager $database;
    protected TransferRepositoryInterface $transferRepository;

    /**
     * TransferService constructor.
     *
     * @param DatabaseManager $database,
     * @param TransferRepositoryInterface $transferRepository
     */
    public function __construct(
        DatabaseManager $database,
        TransferRepositoryInterface $transferRepository,
    ) {
        $this->database = $database;
        $this->transferRepository = $transferRepository;
    }

    /**
     * Execute the transfer between payer and recipient.
     *
     * This method performs a monetary transfer between two users, updating their balances
     * and recording the transaction. It uses a database transaction to ensure consistency
     * and rolls back in case of an error.
     *
     * @param UserData $payer The user initiating the transfer.
     * @param User $recipient The user receiving the transfer.
     * @param float $amount The amount to be transferred.
     * @return void
     * @throws InsufficientFundsException If the user doesnt have enough funds.
     * @throws TransferProcessException If the transfer fails.
     */
    public function process(UserData $payer, User $recipient, float $amount): void
    {
        $connection = $this->database->connection();
        $connection->beginTransaction();

        try {
            $payer = $this->transferRepository->findAndLockUserById($payer->id);
            $recipient = $this->transferRepository->findAndLockUserById($recipient->id);

            if ($payer->balance < $amount) {
                throw new InsufficientFundsException('Saldo insuficiente.');
            }

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
        } catch (InsufficientFundsException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new TransferProcessException('Erro ao processar a transferência.', 500, $e);
        }
    }
}
