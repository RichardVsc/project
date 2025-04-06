<?php

namespace Tests\Unit\Transfer;

use App\Data\UserData;
use App\Exceptions\InsufficientFundsException;
use App\Validators\Transfer\BalanceValidator;
use Mockery;
use Tests\TestCase;

class BalanceValidatorTest extends TestCase
{
    protected $balanceValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceValidator = new BalanceValidator();
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testValidateDoesNotThrowWhenBalanceIsSufficient()
    {
        $payer = new UserData(
            id: 1,
            user_type: 'common',
            balance: 10000
        );

        $this->balanceValidator->validate($payer, 5000);

        $this->assertTrue(true);
    }

    public function testValidateThrowsInsufficientFundsExceptionWhenBalanceIsInsufficient()
    {
        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Saldo insuficiente para realizar a transferência.');

        $payer = new UserData(
            id: 1,
            user_type: 'common',
            balance: 3000
        );

        $this->balanceValidator->validate($payer, 5000);
    }
}
