<?php

namespace Tests\Feature\Transfer;

use App\Data\UserData;
use App\Models\User;
use App\Repositories\Transfer\TransferRepository;
use App\Services\Transfer\TransferProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransferProcessorIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected TransferProcessor $transferProcessor;
    protected TransferRepository $transferRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transferRepository = new TransferRepository();

        $this->transferProcessor = new TransferProcessor(
            DB::getFacadeRoot(),
            $this->transferRepository,
        );
    }

    public function testProcessSuccessfullyTransfersAmount(): void
    {
        $payer = User::factory()->create(['balance' => 1000]);
        $recipient = User::factory()->create(['balance' => 500]);

        $payerData = new UserData(
            id: $payer->id,
            user_type: $payer->user_type,
            balance: $payer->balance
        );

        $amount = 200;

        $this->transferProcessor->process($payerData, $recipient, $amount);

        $this->assertDatabaseHas('users', [
            'id' => $payer->id,
            'user_type' => $payer->user_type,
            'balance' => 800,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $recipient->id,
            'user_type' => $payer->user_type,
            'balance' => 700,
        ]);

        $this->assertDatabaseHas('transfers', [
            'payer' => $payer->id,
            'payee' => $recipient->id,
            'value' => $amount,
        ]);
    }
}
