<?php

namespace App\Services\Transfer;

use App\Models\User;
use App\Exceptions\TransferException;
use App\Repositories\Transfer\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\Notification\NotificationService;

class TransferService
{
    protected $notificationService;
    protected $transferRepository;

    public function __construct(NotificationService $notificationService, TransferRepositoryInterface $transferRepository)
    {
        $this->notificationService = $notificationService;
        $this->transferRepository = $transferRepository;
    }

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


    private function checkBalance(User $payer, float $amount)
    {
        if ($payer->balance < $amount) {
            throw new TransferException('Saldo insuficiente para realizar a transferência.', 400);
        }
    }

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
