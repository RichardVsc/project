<?php

namespace App\Services\Transfer;

use App\Exceptions\RecipientNotFoundException;
use App\Models\User;
use App\Repositories\Transfer\TransferRepositoryInterface;

class RecipientResolver
{
    protected TransferRepositoryInterface $transferRepository;

    /**
     * TransferService constructor.
     *
     * @param TransferRepositoryInterface $transferRepository
     */
    public function __construct(
        TransferRepositoryInterface $transferRepository,
    ) {
        $this->transferRepository = $transferRepository;
    }

    /**
     * Retrieve the recipient user by ID.
     *
     * This method attempts to find the user with the given recipient ID.
     * If the user is not found, it throws a RecipientNotFoundException.
     *
     * @param int $recipientId The ID of the recipient.
     * @return User The recipient user object.
     * @throws RecipientNotFoundException If the recipient is not found.
     */
    public function resolve(int $recipientId): User
    {
        $recipient = $this->transferRepository->findUserById($recipientId);
        if (!$recipient) {
            throw new RecipientNotFoundException('Destinatário da transação não encontrado.', 404);
        }

        return $recipient;
    }
}
