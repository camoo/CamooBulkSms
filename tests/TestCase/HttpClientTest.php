<?php

namespace CamooSms\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\HttpClient;
use Camoo\Sms\Exception\HttpClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
 * Class HttpClientTest
 * @author CamooSarl
 * @covers \Camoo\Sms\HttpClient
 */
class HttpClientTest extends TestCase
{
    private $oClient;

    public function setUp() : void
    {
        $this->oClient = $this->getMockBuilder(HttpClient::class)
            ->setConstructorArgs(['camoo.cm', ['eeee', 'yyyy']])
            ->setMethods(['client'])
            ->getMock();
    }

    /**
     * @dataProvider instanceDataProvider
     */
    public function testInstanceSuccess($endpoint, $hAuth, $timeout)
    {
        $GLOBALS['wp_version'] = '5.3';
        $this->assertInstanceOf(HttpClient::class, new HttpClient($endpoint, $hAuth, $timeout));
    }

    /**
     * @dataProvider instanceDataProviderFailure1
     */
    public function testInstanceFailure1($endpoint, $hAuth, $timeout)
    {
        $this->expectException(HttpClientException::class);
        new HttpClient($endpoint, $hAuth, $timeout);
    }

    /**
     * @dataProvider instanceDataProviderFailure2
     */
    public function testInstanceFailure2($endpoint, $hAuth, $timeout)
    {
        $this->expectException(\TypeError::class);
        new HttpClient($endpoint, $hAuth, $timeout);
    }

    public function instanceDataProvider()
    {
        return [
            ['https://api.camoo.cm/sms.json', ['api_key' => '3i3i', 'api_secret' => '3ueuu4'], 0],
            ['https://api.camoo.cm/sms.xml', ['api_key' => '3ifff3i', 'api_secret' => '3ueuu4'], 10],
        ];
    }

    /**
     * @dataProvider instanceDataProvider
     * @depends testInstanceSuccess
     */
    public function testperformRequestSuccess($endpoint, $hAuth, $timeout)
    {
        $mock = new MockHandler([
                        new Response(200, ['X-Foo' => 'Bar']),
                    ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $http = new HttpClient($endpoint, $hAuth, $timeout);
        $this->assertNotNull($http->performRequest(HttpClient::GET_REQUEST, [], [], $client));
    }

    /**
     * @dataProvider instanceDataProviderFailure3
     * @depends testInstanceSuccess
     */
    public function testperformRequestFailure1($endpoint, $hAuth)
    {
        $this->expectException(HttpClientException::class);
        $http = new HttpClient($endpoint, $hAuth);
        $http->performRequest(HttpClient::POST_REQUEST);
    }

    /**
     * @dataProvider instanceDataProvider
     * @depends testInstanceSuccess
     */
    public function testperformRequestFailure2($endpoint, $hAuth, $timeout)
    {
        $this->expectException(HttpClientException::class);
        $mock = new MockHandler([
                        new RequestException("Error Communicating with Server", new Request('GET', 'test'))
                    ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $http = new HttpClient($endpoint, $hAuth, $timeout);
        $http->performRequest(HttpClient::GET_REQUEST, [], [], $client);
    }

    /**
     * @dataProvider instanceDataProvider
     * @depends testInstanceSuccess
     */
    public function testperformRequestFailure3($endpoint, $hAuth, $timeout)
    {
        $this->expectException(HttpClientException::class);
        if (!defined('WP_CAMOO_SMS_VERSION')) {
            define('WP_CAMOO_SMS_VERSION', 1);
        }
        $mock = new MockHandler([
                        new Response(202, ['Content-Length' => 0])
                    ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $http = new HttpClient($endpoint, $hAuth, $timeout);
        $http->performRequest(HttpClient::GET_REQUEST, [], [], $client);
    }

    public function instanceDataProviderFailure1()
    {
        return [
            ['https://api.camoo.cm', ['api_key' => '3i3i', 'api_secret' => '3ueuu4'], '-10'],
            ['https://api.camoo.cm', ['api_key' => '3ifff3i', 'api_secret' => '3ueuu4'], -1],
        ];
    }

    public function instanceDataProviderFailure2()
    {
        return [
            ['https://api.camoo.cm', ['api_key' => '3i3i', 'api_secret' => '3ueuu4'], 'abc'],
            ['https://api.camoo.cm', ['api_key' => '3ifff3i', 'api_secret' => '3ueuu4'], 'xyz'],
        ];
    }

    public function instanceDataProviderFailure3()
    {
        return [
            ['https://api.camoo.cm', ['api_key' => '3i3i', 'api_secret' => '3ueuu4']],
            ['https://api.camoo.cm', ['api_key' => '3ifff3i', 'api_secret' => '3ueuu4']],
        ];
    }
}
