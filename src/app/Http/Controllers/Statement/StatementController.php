<?php

namespace App\Http\Controllers\Statement;

use App\Http\Controllers\Controller;
use App\Services\Statement\StatementService;
use Illuminate\Support\Facades\Auth;

/**
 * Class StatementController.
 *
 * Handles the retrieval of transaction statements for users.
 */
class StatementController extends Controller
{
    protected $statementService;

    /**
     * StatementController constructor.
     *
     * @param StatementService $statementService
     */
    public function __construct(StatementService $statementService)
    {
        $this->statementService = $statementService;
    }

    /**
     * Display a listing of the user's transaction statements.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $userId = Auth::id();
        $transactions = $this->statementService->getUserStatement($userId);

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions retrieved successfully.',
            'data' => $transactions,
        ], 200);
    }
}
