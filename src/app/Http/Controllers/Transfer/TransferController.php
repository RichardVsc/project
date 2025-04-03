<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use App\Services\Transfer\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    protected $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payer = Auth::user();
        $recipientId = $validated['recipient_id'];
        $amount = $validated['amount'];

        $this->transferService->executeTransfer($payer, $recipientId, $amount);

        return response()->json([
            'status' => 'success',
            'message' => 'Transferência realizada com sucesso!',
            'new_balance' => $payer->balance
        ], 200);
    }
}
