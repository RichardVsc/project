<?php

namespace App\Repositories\Transfer;

use App\Data\UserData;
use App\Models\User;

interface TransferRepositoryInterface
{
    /**
     * Find a user by their ID.
     *
     * This method should retrieve a user by their ID. If no user is found, it should return null.
     *
     * @param int $id
     * @return User|null
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
     * @param User $user
     * @return void
     */
    public function updateUserBalance(User $user): void;

    /**
     * Retrieve the basic data of a user by their ID.
     *
     * This method should return a UserData DTO containing essential information,
     * such as the user's ID and balance.
     *
     * @param int $id
     * @return UserData
     */
    public function getUserDataById(int $id): UserData;

    /**
     * Find and lock a user by their ID for update.
     *
     * This method should return a User model instance locked for update,
     * used to safely perform operations such as balance modifications during a transaction.
     *
     * @param int $id
     * @return User
     */
    public function findAndLockUserById(int $id): User;

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
    public function debitUser(User $user, int $amount): User;

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
    public function creditUser(User $user, int $amount): User;
}
