<?php

namespace App\Repositories\Statement;

use App\Models\Transfer;

/**
 * Class StatementRepository
 * 
 * Handles data retrieval related to user transaction statements.
 * 
 * @package App\Repositories\Statement
 */
class StatementRepository implements StatementRepositoryInterface
{
    /**
     * Retrieve all transactions associated with a given user.
     *
     * This method fetches all transactions where the user is either the payer or the payee,
     * ordered by the most recent transactions first.
     *
     * @param int $userId The ID of the user whose transactions are being retrieved.
     * @return array An array of transactions.
     */
    public function getUserTransactions(int $userId): array
    {
        return Transfer::with(['payerUser', 'payeeUser'])
            ->where('payer', $userId)
            ->orWhere('payee', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}
