<?php

namespace CamooSms\Test\TestCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Error;
use Camoo\Sms\Base;
use Camoo\Sms\Message;
use Camoo\Sms\Exception\CamooSmsException;

/**
 * Class BaseTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Base
 */
class BaseTest extends TestCase
{
    private $oBase;

    public function setUp() : void
    {
        $this->oBase = new Base;
        $this->oAccessTokenMocked = $this->getMockBuilder(Base::class)
            ->setMethods(['apiCall'])
            ->getMock();

        $this->oAccessTokenMocked->expects($this->any())
            ->method('apiCall')
            ->will($this->returnValue(['result' => ['access_token' => time() .'khddkjdhdjdhoid847d_f'], 'code' => 200, 'entity' => null]));
    }

    public function tearDown() : void
    {
        if (file_exists(dirname(dirname(__DIR__)). '/config/app.php')) {
            @unlink(dirname(dirname(__DIR__)). '/config/app.php');
        }
		unset($this->oBase);
    }

    /**
     * @covers \Camoo\Sms\Base::setResourceName
     * @dataProvider resourceDataProvider
     */
    public function testSetResource($data)
    {
        $this->assertNull($this->oBase->setResourceName($data));
    }

    /**
     * @covers \Camoo\Sms\Base::getResourceName
     * @dataProvider resourceDataProvider
     */
    public function testGetResource($data)
    {
        $this->assertNull($this->oBase->setResourceName($data));
        $this->assertEquals($this->oBase->getResourceName(), $data);
    }

    /**
     * @covers \Camoo\Sms\Base::create
     * @dataProvider createDataProvider
     */
    public function testCreate($apikey, $apisecret)
    {
        $this->assertInstanceOf(Base::class, Base::create($apikey, $apisecret));
    }

    /**
     * @covers \Camoo\Sms\Base::create
     */
    public function testCreateException()
    {
        $this->expectException(CamooSmsException::class);
        Base::create();
    }

    /**
     * @covers \Camoo\Sms\Base::create
     */
    public function testCreateConfigFile()
    {
        touch(dirname(dirname(__DIR__)). '/config/app.php');
        $this->assertIsObject(Base::create());
    }

    /**
     * @covers \Camoo\Sms\Base::clear
     * @dataProvider createDataProvider
     */
    public function testCreateObj($apikey,$apisecret)
    {
        $this->assertNull($this->oBase->clear());
        $this->assertIsObject(Message::create($apikey, $apisecret));
    }

    public function resourceDataProvider()
    {
        return [
            ['sms'],
            ['balance']
        ];
    }

    public function createDataProvider()
    {
        return [
            ['fgfgfgfkjf', 'fhkjdfh474gudghjdg74tj4uzt64'],
            ['f9033gfgfgfkjf', '283839383fhkjdfh474gudghjdg74tj4uzt64'],
        ];
    }
}
