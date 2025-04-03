<?php

namespace App\Repositories\Transfer;

use App\Models\User;
use App\Models\Transfer;

class TransferRepository implements TransferRepositoryInterface
{
    /**
     * Find a user by their ID.
     *
     * This method retrieves a user by their ID. If no user is found, it returns null.
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function findUserById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Create a new transfer record.
     *
     * This method creates a new transfer record in the database based on the provided data.
     *
     * @param array $data
     * @return void
     */
    public function createTransfer(array $data): void
    {
        Transfer::create($data);
    }

    /**
     * Update the balance of a given user.
     *
     * This method saves the user model to reflect any changes in their balance.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function updateUserBalance(User $user): void
    {
        $user->save();
    }
}
