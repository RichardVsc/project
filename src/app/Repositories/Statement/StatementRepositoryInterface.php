<?php

namespace App\Repositories\Statement;

/**
 * Interface StatementRepositoryInterface
 * 
 * Defines the contract for retrieving user transaction statements.
 * 
 * @package App\Repositories\Statement
 */
interface StatementRepositoryInterface
{
    /**
     * Retrieve all transactions associated with a given user.
     *
     * @param int $userId The ID of the user whose transactions are being retrieved.
     * @return array An array of transactions.
     */
    public function getUserTransactions(int $userId): array;
}
