<?php

namespace CamooSms\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Balance;
use Camoo\Sms\HttpClient;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Exception\HttpClientException;

/**
 * Class BalanceTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Balance
 */
class BalanceTest extends TestCase
{
    private $oBalance;

    public function setUp() : void
    {
        $this->oBalance = $this->getMockBuilder(Balance::class)
            ->setMethods(['execRequest'])
            ->getMock();
    }

    public function tearDown() : void
    {
        $this->oBalance->clear();
    }

    /**
     * @covers \Camoo\Sms\Balance::get
     */
    public function testGetSuccess()
    {
        $this->oBalance->expects($this->once())
            ->method('execRequest')
            ->with(HttpClient::GET_REQUEST, false)
            ->will($this->returnValue([]));
        $this->assertNotNull($this->oBalance->get());
    }

    /**
     * @covers \Camoo\Sms\Balance::get
     */
    public function testGetFailure()
    {
        $this->expectException(CamooSmsException::class);
        $this->oBalance->expects($this->once())
            ->method('execRequest')
            ->with(HttpClient::GET_REQUEST, false)
            ->will($this->throwException(new CamooSmsException));
        $this->oBalance->get();
    }

    /**
     * @covers \Camoo\Sms\Balance::add
     * @dataProvider addDataProvider
     */
    public function testAddSuccess($data)
    {
        $this->oBalance->phonenumber = $data['phonenumber'];
        $this->oBalance->amount = $data['amount'];
        $this->oBalance->expects($this->once())
            ->method('execRequest')
            ->with(HttpClient::POST_REQUEST)
            ->will($this->returnValue([]));
        $this->assertNotNull($this->oBalance->add());
    }

    /**
     * @covers \Camoo\Sms\Balance::add
     * @dataProvider addDataProvider
     */
    public function testAddFailure($data)
    {
        $this->expectException(CamooSmsException::class);
        $oBalance = Balance::create('api_key', 'secret_key');
        $oBalance->phonenumber = $data['phonenumber'];
        $oBalance->amounts = $data['amount'];
        $oBalance->add();
    }

    public function addDataProvider()
    {
        return [
            [['phonenumber' => 612345678, 'amount' => 1000]],
            [['phonenumber' => 672345678, 'amount' => 3000]],
        ];
    }
}
