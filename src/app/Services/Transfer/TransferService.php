<?php

namespace App\Services\Transfer;

use App\Exceptions\TransferException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransferService
{
    protected $notificationService;
    protected $transferRepository;

    /**
     * TransferService constructor.
     *
     * @param NotificationService $notificationService
     * @param TransferRepositoryInterface $transferRepository
     */
    public function __construct(NotificationService $notificationService, TransferRepositoryInterface $transferRepository)
    {
        $this->notificationService = $notificationService;
        $this->transferRepository = $transferRepository;
    }

    /**
     * Execute a transfer from payer to recipient.
     *
     * This method performs the transfer, updating the payer and recipient balances
     * and creating a transfer record. It also sends a notification to the recipient
     * and handles any transaction failures with a rollback.
     *
     * @param User $payer
     * @param int $recipientId
     * @param float $amount
     * @return void
     * @throws TransferException
     */
    public function executeTransfer(User $payer, int $recipientId, float $amount)
    {
        $this->checkBalance($payer, $amount);
        $this->authorizeTransaction();

        DB::beginTransaction();
        try {
            $recipient = $this->transferRepository->findUserById($recipientId);

            if (!$recipient) {
                throw new TransferException('Recipient not found.', 404);
            }

            $payer->balance -= $amount;
            $this->transferRepository->updateUserBalance($payer);

            $recipient->balance += $amount;
            $this->transferRepository->updateUserBalance($recipient);

            $this->transferRepository->createTransfer([
                'payer' => $payer->id,
                'payee' => $recipient->id,
                'value' => $amount,
            ]);

            $this->notificationService->send($recipient->id, 'Sua transferência foi realizada com sucesso.');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new TransferException('Erro ao processar a transferência.', 500, $e);
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
            $response = Http::get('https://util.devi.tools/api/v2/authorize');

            if (!$response->json('data.authorization')) {
                throw new TransferException('Transação não autorizada pelo serviço externo.', 502);
            }
        } catch (\Exception $e) {
            throw new TransferException('Erro ao consultar serviço autorizador.', 500, $e);
        }
    }
}
