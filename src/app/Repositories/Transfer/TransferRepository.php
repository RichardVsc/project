<?php

namespace App\Repositories\Transfer;

use App\Models\User;
use App\Models\Transfer;

class TransferRepository implements TransferRepositoryInterface
{
    public function findUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function createTransfer(array $data): void
    {
        Transfer::create($data);
    }

    public function updateUserBalance(User $user): void
    {
        $user->save();
    }
}
