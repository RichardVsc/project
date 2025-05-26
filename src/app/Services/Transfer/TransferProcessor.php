<?php

namespace App\Services\Transfer;

use App\Data\UserData;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\Transfer\TransferProcessException;
use App\Mappers\UserDataMapper;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Validators\Transfer\BalanceValidator;
use Illuminate\Database\DatabaseManager;

class TransferProcessor
{
    protected DatabaseManager $database;
    protected TransferRepositoryInterface $transferRepository;
    protected BalanceValidator $balanceValidator;
    protected UserDataMapper $userDataMapper;

    /**
     * TransferService constructor.
     *
     * @param DatabaseManager $database,
     * @param TransferRepositoryInterface $transferRepository
     */
    public function __construct(
        DatabaseManager $database,
        TransferRepositoryInterface $transferRepository,
        BalanceValidator $balanceValidator,
        UserDataMapper $userDataMapper,
    ) {
        $this->database = $database;
        $this->transferRepository = $transferRepository;
        $this->balanceValidator = $balanceValidator;
        $this->userDataMapper = $userDataMapper;
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
     * @param int $amount The amount to be transferred.
     * @return void
     * @throws InsufficientFundsException If the user doesnt have enough funds.
     * @throws TransferProcessException If the transfer fails.
     */
    public function process(UserData $payer, User $recipient, int $amount): void
    {
        $connection = $this->database->connection();
        $connection->beginTransaction();

        try {
            $payer = $this->transferRepository->findAndLockUserById($payer->id);
            $recipient = $this->transferRepository->findAndLockUserById($recipient->id);

            $payerData = $this->userDataMapper->fromModel($payer);
            $this->balanceValidator->validate($payerData, $amount);

            $this->transferRepository->debitUser($payer, $amount);
            $this->transferRepository->creditUser($recipient, $amount);

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
