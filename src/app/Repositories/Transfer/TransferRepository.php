<?php

namespace App\Repositories\Transfer;

use App\Data\UserData;
use App\Exceptions\InsufficientFundsException;
use App\Models\Transfer;
use App\Models\User;

class TransferRepository implements TransferRepositoryInterface
{
    /**
     * Find a user by their ID.
     *
     * This method retrieves a user by their ID. If no user is found, it returns null.
     *
     * @param int $id
     * @return User|null
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
     * @param User $user
     * @return void
     */
    public function updateUserBalance(User $user): void
    {
        $user->save();
    }

    /**
     * Retrieve user data by ID.
     *
     * This method fetches a user by their ID and returns a simplified data object (UserData)
     * containing only essential information such as ID and balance.
     *
     * @param int $id The ID of the user to retrieve.
     * @return UserData The user data transfer object.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found.
     */
    public function getUserDataById(int $id): UserData
    {
        $user = User::findOrFail($id);

        return new UserData($user->id, $user->user_type, $user->balance);
    }

    /**
     * Find and lock a user by ID for update.
     *
     * This method retrieves a user record by its ID and applies a database-level
     * lock (FOR UPDATE) to prevent race conditions during critical operations,
     * such as balance updates during transfers.
     *
     * @param int $id The ID of the user to lock.
     * @return User The locked user model instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found.
     */
    public function findAndLockUserById(int $id): User
    {
        return User::where('id', $id)->lockForUpdate()->firstOrFail();
    }

    /**
     * Debit a specified amount from the user's balance.
     *
     * This method checks if the user has sufficient balance. If not,
     * it throws an InsufficientFundsException. Upon successful validation,
     * it deducts the amount from the user's balance and persists the change.
     *
     * @param User $user  The user whose balance will be debited.
     * @param int $amount  The amount to be debited.
     *
     * @return User  The updated User instance with the new balance.
     */
    public function debitUser(User $user, int $amount): User
    {
        $user->balance -= $amount;
        $this->updateUserBalance($user);

        return $user;
    }

    /**
     * Credit a specified amount to the user's balance.
     *
     * This method increases the user's balance by the given amount
     * and persists the change to the database.
     *
     * @param User $user  The user whose balance will be credited.
     * @param int $amount  The amount to be credited.
     *
     * @return User  The updated User instance with the new balance.
     */
    public function creditUser(User $user, int $amount): User
    {
        $user->balance += $amount;
        $this->updateUserBalance($user);

        return $user;
    }
}
