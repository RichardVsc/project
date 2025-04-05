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
}
