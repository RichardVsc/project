<?php

namespace App\Repositories\Transfer;

use App\Models\User;

interface TransferRepositoryInterface
{
    /**
     * Find a user by their ID.
     *
     * This method should retrieve a user by their ID. If no user is found, it should return null.
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function findUserById(int $id): ?User;

    /**
     * Create a new transfer record.
     *
     * This method should create a new transfer record in the database based on the provided data.
     *
     * @param array $data
     * @return void
     */
    public function createTransfer(array $data): void;

    /**
     * Update the balance of a given user.
     *
     * This method should save the user model to reflect any changes in their balance.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function updateUserBalance(User $user): void;
}
