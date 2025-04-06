<?php

namespace Tests\Unit\Transfer;

use App\Data\UserData;
use App\Exceptions\MerchantCannotTransferException;
use App\Validators\Transfer\PayerTypeValidator;
use Mockery;
use PHPUnit\Framework\TestCase;

class PayerTypeValidatorTest extends TestCase
{
    protected $payerTypeValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payerTypeValidator = new PayerTypeValidator();
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

        $this->payerTypeValidator->validate($payer);

        $this->assertTrue(true);
    }

    public function testValidateThrowsInsufficientFundsExceptionWhenBalanceIsInsufficient()
    {
        $this->expectException(MerchantCannotTransferException::class);
        $this->expectExceptionMessage('Lojistas não podem realizar transferências.');

        $payer = new UserData(
            id: 1,
            user_type: 'merchant',
            balance: 3000
        );

        $this->payerTypeValidator->validate($payer);
    }
}
