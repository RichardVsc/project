<?php

namespace App\Http\Controllers\Transfer;

use App\Data\TransferRequestData;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Transfer\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    protected TransferService $transferService;

    /**
     * TransferController constructor.
     *
     * @param TransferService $transferService
     */
    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Store a new transfer request.
     *
     * Handles the process of validating the transfer data, checking user balances,
     * and calling the TransferService to execute the transfer.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payer = Auth::user();
        $recipientId = $validated['recipient_id'];
        $amount = (int) round($validated['amount'] * 100);

        $data = new TransferRequestData(
            payerId: $payer->id,
            recipientId: $recipientId,
            amount: $amount
        );

        $this->transferService->transfer($data);

        $payer = User::find(Auth::id());
        $payer = $payer->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Transferência realizada com sucesso!',
            'new_balance' => $payer->balance,
        ], 200);
    }
}
