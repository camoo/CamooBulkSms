<?php

namespace CamooSms\Test\TestCase\Objects;

use PHPUnit\Framework\TestCase;
use Valitron\Validator;
use Camoo\Sms\Objects\Balance;

/**
 * Class BalanceTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Objects\Balance
 */
class BalanceTest extends TestCase
{

    /**
     * @covers \Camoo\Sms\Objects\Balance::validatorDefault
     * @dataProvider defaultDataProviderSuccess
     */
    public function testValidatorDefaultSuccess($payload)
    {
        $oValidator = (new Balance)->validatorDefault(new Validator($payload));
        $this->assertInstanceOf(Validator::class, $oValidator);
        $this->assertTrue($oValidator->validate());
    }

    /**
     * @covers \Camoo\Sms\Objects\Balance::validatorDefault
     * @dataProvider defaultDataProviderFailure
     */
    public function testValidatorDefaultFailure($payload)
    {
        $oValidator = (new Balance)->validatorDefault(new Validator($payload));
        $this->assertInstanceOf(Validator::class, $oValidator);
        $this->assertFalse($oValidator->validate());
    }

    public function defaultDataProviderFailure()
    {
        return [
			[['amount' => 1000, 'phonenumber' => '691243568']],
			[['amount' => 'foo', 'phonenumber' => '671243568']],
        ];
    }

    public function defaultDataProviderSuccess()
    {
        return [
			[['amount' => 3000, 'phonenumber' => '671243568']],
        ];
    }

}
