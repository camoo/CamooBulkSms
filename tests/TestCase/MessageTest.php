<?php

namespace CamooSms\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\HttpClient;
use Camoo\Sms\Message;
use Camoo\Sms\Constants;

/**
 * Class MessageTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Message
 */
class MessageTest extends TestCase
{
    private $oMessage;

    public function setUp() : void
    {
        $this->oMessage = $this->getMockBuilder(Message::class)
            ->setMethods(['execRequest'])
            ->getMock();
    }

    public function tearDown() : void
    {
        $this->oMessage->clear();
    }

    /**
     * @covers \Camoo\Sms\Message::send
     */
    public function testSendSuccess()
    {
        $this->oMessage->from ='YourCompany';
        $this->oMessage->to = '+237612345678';
        $this->oMessage->message ='Hello Kmer World! Déjà vu!';
        $this->oMessage->expects($this->once())
            ->method('execRequest')
            ->with(HttpClient::POST_REQUEST)
            ->will($this->returnValue([]));
        $this->assertNotNull($this->oMessage->send());
    }

    /**
     * @covers \Camoo\Sms\Message::send
     * @dataProvider createDataProvider
     */
    public function testSendFailure($apikey, $apisecret)
    {
        $this->expectException(CamooSmsException::class);
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->tel = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';
        $oMessage->send();
    }

    /**
     * @covers \Camoo\Sms\Message::view
     */
    public function testViewSuccess()
    {
        //    $this->oMessage->clear();
        $this->oMessage->id = '12293kp';
        $this->oMessage->expects($this->once())
            ->method('execRequest')
            ->with(HttpClient::GET_REQUEST, true, Constants::RESOURCE_VIEW)
            ->will($this->returnValue([]));
        $this->assertNotNull($this->oMessage->view());
    }

    /**
     * @covers \Camoo\Sms\Message::view
     * @dataProvider createDataProvider
     */
    public function testViewFailure($apikey, $apisecret)
    {
        //    $this->oMessage->clear();
        $this->expectException(CamooSmsException::class);
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->to = '12293kp';
        $oMessage->view();
    }

    /**
     * @covers \Camoo\Sms\Message::view
     * @dataProvider createDataProvider
     */
    public function testsendBulkFailureFalse($apikey, $apisecret)
    {
        $oMessage = Message::create($apikey, $apisecret);
        $this->assertFalse($oMessage->sendBulk());
    }

    /**
     * @covers \Camoo\Sms\Message::view
     * @dataProvider createDataProvider
     */
    public function testsendBulkSucess($apikey, $apisecret)
    {
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->to = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'];
        $this->assertIsInt($oMessage->sendBulk([]));
    }

    public function createDataProvider()
    {
        return [
            ['fgfgfgfkjf', 'fhkjdfh474gudghjdg74tj4uzt64'],
            ['f9033gfgfgfkjf', '283839383fhkjdfh474gudghjdg74tj4uzt64'],
        ];
    }
}
