<?php

namespace CamooSms\Test\TestCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Error;
use Camoo\Sms\Base;
use Camoo\Sms\Message;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Objects;
use Camoo\Sms\HttpClient;
use Camoo\Sms\Exception\HttpClientException;

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
    }

    public function tearDown() : void
    {
        if (file_exists(dirname(dirname(__DIR__)). '/config/app.php')) {
            @unlink(dirname(dirname(__DIR__)). '/config/app.php');
        }
        if (file_exists('/tmp/test.pem')) {
            @unlink('/tmp/test.pem');
        }
        if (file_exists('/tmp/test2.pem')) {
            @unlink('/tmp/test.pem');
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
    public function testCreateObj($apikey, $apisecret)
    {
        $this->assertNull($this->oBase->clear());
        $this->assertIsObject(Message::create($apikey, $apisecret));
    }

    /**
     * @covers \Camoo\Sms\Base::getDataObject
     * @dataProvider createDataProvider
     * @depends testCreateObj
     */
    public function testgetDataObject($apikey, $apisecret)
    {
        $this->assertInstanceOf(Objects\Message::class, $this->oBase->getDataObject());
    }

    /**
     * @covers \Camoo\Sms\Base::getConfigs
     * @dataProvider createDataProvider
     * @depends testCreateObj
     */
    public function testgetConfigs($apikey, $apisecret)
    {
        $this->assertIsArray($this->oBase->getConfigs());
    }

    /**
     * @covers \Camoo\Sms\Base::getCredentials
     * @dataProvider createDataProvider
     * @depends testCreateObj
     */
    public function testgetCredentials($apikey, $apisecret)
    {
        $this->assertIsArray($this->oBase->getCredentials());
    }

    /**
     * @covers \Camoo\Sms\Base::getData
     * @dataProvider createDataProvider
     * @depends testCreateObj
     */
    public function testgetData($apikey, $apisecret)
    {
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';
        $this->assertIsArray($this->oBase->getData());
    }

    /**
     * @covers \Camoo\Sms\Base::getData
     * @dataProvider createDataProvider
     * @depends testCreateObj
     */
    public function testgetDataException($apikey, $apisecret)
    {
        //$this->expectException(CamooSmsException::class);
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->tel = '+23712345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';
        $this->assertIsArray($oMessage->getData());
        $this->assertEquals([], $oMessage->getData());
    }

    /**
     * @covers \Camoo\Sms\Base::getData
     * @dataProvider createDataProvider
     * @depends testCreateObj
     */
    public function testgetDataGet($apikey, $apisecret)
    {
        //$this->expectException(CamooSmsException::class);
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';
        $this->assertEquals($this->equalTo($oMessage->from), $this->equalTo('YourCompany'));
    }

    /**
     * @covers \Camoo\Sms\Base::getEndPointUrl
     * @dataProvider resourceDataProvider
     */
    public function testgetEndPointUrl($data)
    {
        $this->oBase->setResourceName($data);
        $this->assertStringContainsString('api.camoo.cm', $this->oBase->getEndPointUrl());
    }

    /**
     * @covers \Camoo\Sms\Base::setResponseFormat
     * @dataProvider formatDataProvider
     */
    public function testsetResponseFormat($data)
    {
        $this->assertNull($this->oBase->setResponseFormat($data));
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     * @depends testCreateObj
     */
    public function testexecRequest($apikey, $apisecret)
    {
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';

        $this->oClientMock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['performRequest'])
            ->setConstructorArgs([$this->oBase->getEndPointUrl(), $this->oBase->getCredentials()])
            ->getMock();

        $this->oClientMock->expects($this->once())
            ->method('performRequest')
            ->will($this->returnValue('{"test":"OK"}'));
 
        $this->assertNotNull($oMessage->execRequest(HttpClient::POST_REQUEST, true, null, $this->oClientMock));
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestEnc($apikey, $apisecret)
    {
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->encrypt = true;
        $oMessage->message ='Hello Kmer World! Déjà vu!';

        $this->oClientMock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['performRequest'])
            ->setConstructorArgs([$this->oBase->getEndPointUrl(), $this->oBase->getCredentials()])
            ->getMock();

        $this->oClientMock->expects($this->once())
            ->method('performRequest')
            ->will($this->returnValue('{"test":"OK"}'));
 
        $this->assertNotNull($oMessage->execRequest(HttpClient::POST_REQUEST, true, null, $this->oClientMock));
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestException1($apikey, $apisecret)
    {
        $this->expectException(CamooSmsException::class);
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->tel = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';

        $oMessage->execRequest(HttpClient::POST_REQUEST);
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestException2($apikey, $apisecret)
    {
        $this->expectException(CamooSmsException::class);
        $this->oBase->clear();
        $oMessage = Message::create($apikey.'epi2', $apisecret .'epi2');
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';

        $this->oClientMock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['performRequest'])
            ->setConstructorArgs([$this->oBase->getEndPointUrl(), $this->oBase->getCredentials()])
            ->getMock();

        $this->oClientMock->expects($this->once())
            ->method('performRequest')
            ->will($this->throwException(new HttpClientException));
        $oMessage->execRequest(HttpClient::POST_REQUEST, true, null, $this->oClientMock);
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestJson($apikey, $apisecret)
    {
        //$this->expectException(Error::class);
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';

        $this->oClientMock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['performRequest'])
            ->setConstructorArgs([$this->oBase->getEndPointUrl(), $this->oBase->getCredentials()])
            ->getMock();

        $this->oClientMock->expects($this->once())
            ->method('performRequest')
            ->will($this->returnValue('Error json'));
 
        $this->assertNull($oMessage->execRequest(HttpClient::POST_REQUEST, true, null, $this->oClientMock));
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestXml($apikey, $apisecret)
    {
        $this->oBase->clear();
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';
        $this->oBase->setResponseFormat('xml');

        $this->oClientMock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['performRequest'])
            ->setConstructorArgs([$this->oBase->getEndPointUrl(), $this->oBase->getCredentials()])
            ->getMock();

        $this->oClientMock->expects($this->once())
            ->method('performRequest')
            ->will($this->returnValue('<ul><li>Test</li></ul>'));
 
        $this->assertNotNull($oMessage->execRequest(HttpClient::POST_REQUEST, true, null, $this->oClientMock));
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestXmlFailure($apikey, $apisecret)
    {
        //$this->expectException(Error::class);
        $this->oBase->clear();
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->message ='Hello Kmer World! Déjà vu!';
        $this->oBase->setResponseFormat('xml');

        $this->oClientMock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['performRequest'])
            ->setConstructorArgs([$this->oBase->getEndPointUrl(), $this->oBase->getCredentials()])
            ->getMock();

        $this->oClientMock->expects($this->once())
            ->method('performRequest')
            ->will($this->returnValue(''));
 
        $this->assertNull($oMessage->execRequest(HttpClient::POST_REQUEST, true, null, $this->oClientMock));
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestEncFailure1($apikey, $apisecret)
    {
        touch('/tmp/test.pem');
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->encrypt = true;
        $oMessage->pgp_public_file = '/tmp/test.pem';
        $oMessage->message ='Hello Kmer World! Déjà vu!';

        $this->oClientMock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['performRequest'])
            ->setConstructorArgs([$this->oBase->getEndPointUrl(), $this->oBase->getCredentials()])
            ->getMock();

        $this->oClientMock->expects($this->once())
            ->method('performRequest')
            ->will($this->returnValue('{"test":"OK"}'));
 
        $this->assertNotNull($oMessage->execRequest(HttpClient::POST_REQUEST, true, null, $this->oClientMock));
    }

    /**
     * @covers \Camoo\Sms\Base::execRequest
     * @dataProvider createDataProvider
     */
    public function testexecRequestEncFailure2($apikey, $apisecret)
    {
        file_put_contents('/tmp/test2.pem', 'TEST');
        $this->expectException(CamooSmsException::class);
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = '+237612345678';
        $oMessage->encrypt = true;
        $oMessage->pgp_public_file = '/tmp/test2.pem';
        $oMessage->message ='Hello Kmer World! Déjà vu!';
        $this->assertNotNull($oMessage->execRequest(HttpClient::POST_REQUEST, true, null));
    }

    /**
     * @covers \Camoo\Sms\Base::execBulk
     * @dataProvider createDataProvider
     */
    public function testexecBulk($apikey, $apisecret)
    {
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->to = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'];
        $this->assertIsInt($oMessage->execBulk([]));
    }

    /**
     * @covers \Camoo\Sms\Base::execBulk
     * @dataProvider createDataProvider
     */
    public function testexecBulkFailure($apikey, $apisecret)
    {
        $this->assertNull($this->oBase->clear());
        $oMessage = Message::create($apikey, $apisecret);
        $oMessage->from ='YourCompany';
        $oMessage->tel = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'];
        $this->assertFalse($oMessage->execBulk([]));
    }

    public function resourceDataProvider()
    {
        return [
            ['sms'],
            ['balance']
        ];
    }

    public function formatDataProvider()
    {
        return [
            ['xml'],
            ['json']
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
