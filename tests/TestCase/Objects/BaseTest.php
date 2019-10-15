<?php

namespace CamooSms\Test\TestCase\Objects;

use PHPUnit\Framework\TestCase;
use Valitron\Validator;
use Camoo\Sms\Objects\Base;
use Camoo\Sms\Objects\Message;
use Camoo\Sms\Objects\Balance;
use Camoo\Sms\Lib\Utils;
use Camoo\Sms\Exception\CamooSmsException;

/**
 * Class BaseTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Objects\Base
 */
class BaseTest extends TestCase
{
    private $oBase;

    public function setUp() : void
    {
        $this->oBase = Base::create();
    }

    public function tearDown() : void
    {
        unset($this->oBase);
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::create
     * @runInSeparateProcess
     */
    public function testCreate()
    {
        $this->assertInstanceOf(Base::class, Base::create());
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::set
     * @runInSeparateProcess
     * @dataProvider setDataProviderSuccess
     */
    public function testSetSuccess($property, $value, $object)
    {
        $this->assertNull($this->oBase->set($property, $value, $object));
        if ($property === 'to') {
            $value = Utils::makeNumberE164Format($value);
        }
        $this->assertEquals($value, $object->{$property});
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::set
     * @runInSeparateProcess
     * @testWith        ["test", 4, null]
     */
    public function testSetNull($property, $value, $object)
    {
        $this->assertNull($this->oBase->set($property, $value, $object));
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::set
     * @runInSeparateProcess
     * @dataProvider setDataProviderFailure
     */
    public function testSetFailure($property, $value, $object)
    {
        $this->expectException(CamooSmsException::class);
        $this->oBase->set($property, $value, $object);
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::get
     * @runInSeparateProcess
     * @dataProvider getDataProviderSuccess
     */
    public function testGetSuccess($sets, $object)
    {
        array_map(function ($key, $set) use ($object) {
            $this->oBase->set($key, $set, $object);
        }, array_keys($sets), array_values($sets));

        $result = $this->oBase->get($object);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::get
     * @runInSeparateProcess
     * @dataProvider getDataProviderFailure
     */
    public function testGetFailure($sets, $object)
    {
        $this->expectException(CamooSmsException::class);
        array_map(function ($key, $set) use ($object) {
            $this->oBase->set($key, $set, $object);
        }, array_keys($sets), array_values($sets));

        $result = $this->oBase->get($object);
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::get
     * @runInSeparateProcess
     * @testWith        [null]
     *                  [""]
     *                  [0]
     */
    public function testGetEmpty($object)
    {
        $result = $this->oBase->get($object);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::isMTNCameroon
     * @runInSeparateProcess
     * @testWith        ["0"]
     *                  ["693123456"]
     */
    public function testIsMTNCameroonFailure($to)
    {
        $oValidator = new Validator(['message' => 'foo', 'from' => 'Bar', 'to' => $to]);
        $this->assertNull(Base::create()->isMTNCameroon($oValidator, 'to'));
        $this->assertFalse($oValidator->validate());
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::isMTNCameroon
     * @runInSeparateProcess
     * @dataProvider isUTF8DataProviderFailure
     */
    public function testIsValidUTF8Encoded($message)
    {
        $oValidator = new Validator(['message' => $message]);
        $this->assertNull(Base::create()->isValidUTF8Encoded($oValidator, 'message'));
        $this->assertFalse($oValidator->validate());
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::has
     * @runInSeparateProcess
     * @testWith        ["to"]
     *                  ["tel"]
     */
    public function testHas($property)
    {
        $this->assertIsBool(Message::create()->has($property));
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::isPossibleNumber
     * @runInSeparateProcess
     * @testWith        ["0"]
     */
    public function testIsPossibleNumberFailure($to)
    {
        $oValidator = new Validator(['message' => 'foo', 'from' => 'Bar', 'to' => $to]);
        $this->assertNull(Base::create()->isPossibleNumber($oValidator, 'to'));
        $this->assertFalse($oValidator->validate());
    }

    public function isUTF8DataProviderFailure()
    {
        return [
            [file_get_contents('https://www.cl.cam.ac.uk/~mgk25/ucs/examples/UTF-8-test.txt')],
        ];
    }

    /**
     * @covers \Camoo\Sms\Objects\Base::notEmptyRule
     * @runInSeparateProcess
     * @testWith        ["0"]
     *                  ["tel"]
     *                  [""]
     */
    public function testNotEmptyRule($value)
    {
        $oValidator = new Validator(['id' => $value]);
        $this->assertNull(Base::create()->notEmptyRule($oValidator, 'id'));
        $this->assertIsBool($oValidator->validate());
    }

    public function getDataProviderSuccess()
    {
        return [
            [['to' => '237612345678', 'from' => 'Foo', 'message' => 'Foo Bar'], new Message],
            [['to' => '237672345678', 'from' => 'Foo', 'message' => 'Foo Bar', 'route' => 'classic'], new Message],
            [['phonenumber' => '672345678', 'amount' => 3000], new Balance],
        ];
    }

    public function getDataProviderFailure()
    {
        return [
            [['to' => '612345678', 'from' => 'Foo', 'message' => 'Foo Bar'], new Message],
            [['to' => '33672345678', 'from' => 'Foo', 'message' => 'Foo Bar', 'route' => 'classic'], new Message],
            [['phonenumber' => '692345678', 'amount' => 3000], new Balance],
        ];
    }

    public function setDataProviderSuccess()
    {
        return [
            ['from', 'FooBar', new Message],
            ['to', '237612345678', new Message],
            ['to', ['237612345678','33612345678'], new Message],
            ['to', [['mobile' => '237612345678', 'name' => 'John Doe'],['mobile' => '33612345678', 'Jeanne Doe']], new Message],
            ['phonenumber', 612345678, new Balance],
        ];
    }

    public function setDataProviderFailure()
    {
        return [
            ['sender', 'FooBar', new Message],
            ['phone', '237612345678', new Message],
            ['tel', ['237612345678','33612345678'], new Message],
            ['sms', [['mobile' => '237612345678', 'name' => 'John Doe'],['mobile' => '33612345678', 'Jeanne Doe']], new Message],
            ['to', 612345678, new Balance],
        ];
    }
}
