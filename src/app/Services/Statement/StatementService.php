<?php

namespace App\Services\Statement;

use App\Repositories\Statement\StatementRepositoryInterface;

/**
 * Class StatementService
 * 
 * Handles business logic related to retrieving user transaction statements.
 * 
 * @package App\Services
 */
class StatementService
{
    /**
     * The statement repository instance.
     *
     * @var StatementRepositoryInterface
     */
    protected $repository;

    /**
     * StatementService constructor.
     *
     * @param StatementRepositoryInterface $repository
     */
    public function __construct(StatementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Retrieve the transaction statement for a given user.
     *
     * @param int $userId The ID of the user.
     * @return array The list of transactions.
     */
    public function getUserStatement(int $userId): array
    {
        return $this->repository->getUserTransactions($userId);
    }
}
