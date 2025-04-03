<?php

namespace App\Repositories\Transfer;

use App\Models\User;

interface TransferRepositoryInterface
{
    public function findUserById(int $id): ?User;
    public function createTransfer(array $data): void;
    public function updateUserBalance(User $user): void;
}
