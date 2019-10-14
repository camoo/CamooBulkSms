<?php

namespace CamooSms\Test\TestCase\Objects;

use PHPUnit\Framework\TestCase;
use Valitron\Validator;
use Camoo\Sms\Objects\Message;

/**
 * Class MessageTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Objects\Message
 */
class MessageTest extends TestCase
{
    private $oMessage;

    public function setUp() : void
    {
        $this->oMessage = new Message;
    }

    public function tearDown() : void
    {
        unset($this->oMessage);
    }

    /**
     * @covers \Camoo\Sms\Objects\Message::validatorDefault
     * @dataProvider defaultDataProviderSuccess
     */
    public function testValidatorDefaultSuccess($payload)
    {
        $oValidator = $this->oMessage->validatorDefault(new Validator($payload));
        $this->assertInstanceOf(Validator::class, $oValidator);
        $this->assertTrue($oValidator->validate());
    }

    /**
     * @covers \Camoo\Sms\Objects\Message::validatorDefault
     * @dataProvider defaultDataProviderFailure
     */
    public function testValidatorDefaultFailure($payload)
    {
        $oValidator = $this->oMessage->validatorDefault(new Validator($payload));
        $this->assertInstanceOf(Validator::class, $oValidator);
        $this->assertFalse($oValidator->validate());
    }

    /**
     * @covers \Camoo\Sms\Objects\Message::validatorView
     * @dataProvider viewDataProviderFailure
     */
    public function testValidatorViewFailure($payload)
    {
        $oValidator = $this->oMessage->validatorView(new Validator($payload));
        $this->assertInstanceOf(Validator::class, $oValidator);
        $this->assertFalse($oValidator->validate());
    }

    /**
     * @covers \Camoo\Sms\Objects\Message::validatorView
     * @dataProvider viewDataProviderSuccess
     */
    public function testValidatorViewSuccess($payload)
    {
        $oValidator = $this->oMessage->validatorView(new Validator($payload));
        $this->assertInstanceOf(Validator::class, $oValidator);
        $this->assertTrue($oValidator->validate());
    }

    public function viewDataProviderSuccess()
    {
        return [
            [['id' => 'fh84948fiif']],
            [['id' => 123456]],
        ];
    }

    public function viewDataProviderFailure()
    {
        return [
            [['id' => '']],
            [['id' => null]],
            [['id' => 0]],
        ];
    }

    public function defaultDataProviderFailure()
    {
        return [
            [['to' => [691243568], 'message' => 'foo bar', 'from' => 'FooBar']],
            [['to' => [237691243568], 'message' => 'foo bar', 'from' => 'FooBar', 'pgp_public_file' => '/tmp/test.pub']],
        ];
    }

    public function defaultDataProviderSuccess()
    {
        return [
            [['to' => [237691243568], 'message' => 'foo bar', 'from' => 'FooBar']],
            [['to' => [['mobile' => 237691243568]], 'message' => 'foo bar', 'from' => 'FooBar']],
            [['to' => [237691243568,4917610830190], 'message' => 'foo bar', 'from' => 'FooBar']],
        ];
    }
}
