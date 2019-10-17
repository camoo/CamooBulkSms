<?php

namespace CamooSms\Test\TestCase\Lib;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Lib\Utils;
use \libphonenumber\PhoneNumberUtil;
use \libphonenumber\PhoneNumber;
use stdClass;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Database\MySQL;
use Camoo\Sms\Message;
use PHPUnit\Framework\Error\Error;

/**
 * Class UtilsTest
 * @author CamooSarl
 * @covers Camoo\Sms\Lib\Utils
 */
class UtilsTest extends TestCase
{
    /**
     * @covers \Camoo\Sms\Lib\Utils::satanizer
     * @backupGlobals disabled
     * @dataProvider satanizerDataProviderFailure1
     */
    public function testSatanizerFailure1($xData)
    {
        $this->assertEmpty(Utils::satanizer($xData));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::satanizer
     * @backupGlobals disabled
     * @dataProvider satanizerDataProviderFailure2
     */
    public function testSatanizerFailure2($xData)
    {
        $this->assertEmpty(Utils::satanizer($xData));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::satanizer
     * @backupGlobals disabled
     */
    public function testSatanizerSuccess()
    {
        $this->assertIsString(Utils::satanizer('<b>foo</b>'));
        $this->assertIsString(Utils::satanizer('<b/bar'));
        $this->assertIsString(Utils::satanizer('%abcdef09'));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::stripAllTags
     * @backupGlobals disabled
     * @dataProvider stripAllTagsDataProvider
     */
    public function testStripAllTags($string, $remove_breaks)
    {
        $this->assertIsString(Utils::stripAllTags($string, $remove_breaks));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::decodeJson
     * @backupGlobals disabled
     */
    public function testDecodeJsonFailure()
    {
        $this->assertNull(Utils::decodeJson(''));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::phoneUtil
     */
    public function testPhoneUtil()
    {
        $this->assertInstanceOf(PhoneNumberUtil::class, Utils::phoneUtil());
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::getNumberProto
     * @dataProvider numberProviderSuccess
     */
    public function testGetNumberProtoSuccess($tel, $ccode)
    {
        $this->assertInstanceOf(PhoneNumber::class, Utils::getNumberProto($tel, $ccode));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::getNumberProto
     * @dataProvider numberProviderFailure
     */
    public function testGetNumberProtoFailure($tel, $ccode)
    {
        $this->assertNull(Utils::getNumberProto($tel, $ccode));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::isValidPhoneNumber
     * @dataProvider validNumberProviderSuccess
     */
    public function testIsValidPhoneNumber($tel, $ccode, $strict)
    {
        $this->assertTrue(Utils::isValidPhoneNumber($tel, $ccode, $strict));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::getPhoneRcode
     * @dataProvider numberProviderSuccess
     */
    public function testGetPhoneRcode($tel, $ccode)
    {
        $this->assertIsString(Utils::getPhoneRcode(Utils::getNumberProto($tel, $ccode)));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::getPhoneCcode
     * @dataProvider numberProviderSuccess
     */
    public function testGetPhoneCcode($tel, $ccode)
    {
        $this->assertIsInt(Utils::getPhoneCcode(Utils::getNumberProto($tel, $ccode)));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::isCmMTN
     * @dataProvider mtnProviderSuccess
     */
    public function testIsCmMTNSuccess($tel)
    {
        $this->assertTrue(Utils::isCmMTN($tel));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::isCmMTN
     * @dataProvider mtnProviderFailure
     */
    public function testIsCmMTNFailure($tel)
    {
        $this->assertFalse(Utils::isCmMTN($tel));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::clearSender
     * @dataProvider senderProvider
     */
    public function testclearSender($sender)
    {
        $this->assertIsString(Utils::clearSender($sender));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::normaliseKeys
     */
    public function testnormaliseKeys()
    {
        $rep = [
            'sms' => [
                'messages' => [
                'message-id' => '1763763',
                ]
            ]
        ];
        $this->assertInstanceOf(\stdClass::class, Utils::normaliseKeys($rep));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::getMessageKey
     */
    public function testgetMessageKey()
    {
        $rep = [
            'sms' => [
                'messages' => [
                    [
                        'message-id' => '1763763',
                    ]
                ]
            ]
        ];
        $oResponse = Utils::normaliseKeys($rep);
        $this->assertNull(Utils::getMessageKey($oResponse, 'user_id'));
        $this->assertEquals(Utils::getMessageKey($oResponse, 'message_id'), '1763763');
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::randomStr
     */
    public function testrandomStr()
    {
        $this->assertIsString(Utils::randomStr());
        $this->assertNotNull(Utils::randomStr());
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::decodeJson
     */
    public function testDecodeJson()
    {
        $this->assertIsArray(Utils::decodeJson('{"test":"ok"}', true));
        $this->assertInstanceOf(\stdClass::class, Utils::decodeJson('{"test":"ok"}'));
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::isMultiArray
      */
    public function testisMultiArraySuccess()
    {
        $rep = [
            'sms' => [
                'messages' => [
                    [
                        'message-id' => '1763763',
                    ]
                ]
            ]
        ];
        $this->assertTrue(Utils::isMultiArray($rep));
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::mapMobile
      */
    public function testmapMobile()
    {
        $hTo1 = ['name' => 'John Doe', 'mobile' => '00237612345678'];
        $hTo2 = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'];
        $this->assertRegExp('/^\+/', Utils::mapMobile($hTo1));
        $this->assertStringContainsString('237612345678', Utils::mapMobile($hTo1));
        $this->assertNull(Utils::mapMobile($hTo2));
        $this->assertEquals(Utils::mapMobile('+272982978'), '+272982978');
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::makeNumberE164Format
      * @dataProvider makeNumberE164FormatData
      */
    public function testmakeNumberE164Format($data)
    {
        $this->assertIsArray(Utils::makeNumberE164Format($data));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::phoneNumberE164Format
     * @dataProvider mtnProviderSuccess
     */
    public function testphoneNumberE164Format($tel)
    {
        $this->assertRegExp('/^\+/', Utils::phoneNumberE164Format($tel));
    }

    /**
     * @covers \Camoo\Sms\Lib\Utils::phoneNumberE164Format
     */
    public function testphoneNumberE164FormatNull()
    {
        $this->assertNull(Utils::phoneNumberE164Format(''));
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::isMultiArray
      * @dataProvider isMultiArrayProviderFailure
      */
    public function testisMultiArrayFailure($option)
    {
        $this->assertFalse(Utils::isMultiArray($option));
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::backgroundProcess
      * @dataProvider backgroundProcessData
      */
    public function testbackgroundProcess($option)
    {
        $this->assertIsInt(Utils::backgroundProcess([], [], $option));
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::doBulkCallback
      */
    public function testdoBulkCallbackSuccess()
    {
        $dbMock = $this->getMockBuilder(MySQL::class)
            ->setMethods(['insert','close'])
            ->getMock();

        $dbMock->expects($this->any())
            ->method('insert')
            ->will($this->returnValue(true));

        $dbMock->expects($this->once())
            ->method('close')
            ->will($this->returnValue(true));

        $data = [
            'message' => 'foo Bar',
            'to' => '237612345678',
            'message_id' => 'aas373737',
            'from' => 'YourCompany',
        ];
        $hCallback = [
            'driver' => [\Camoo\Sms\Database\MySQL::class, 'getInstance'],
            'bulk_chunk' => 1,
            'db_config' => [
            [
            'db_name'     => 'test',
            'db_user'     => 'test',
            'db_password' => 'secret',
            'db_host'     => 'localhost',
            'table_sms'   => 'my_table',
            ]
        ],
        'variables' => [
     //Your DB keys => Map camoo keys
        'message'    => 'message',
        'recipient'  => 'to',
        'message_id' => 'message_id',
        'sender'	 => 'from'
            ]
        ];
        $this->assertNull(Utils::doBulkCallback($hCallback, $data, $dbMock));
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::doBulkCallback
      */
    public function testdoBulkCallbackFailure()
    {
        $this->expectException(Error::class);
        $data = [
            'message' => 'foo Bar',
            'to' => '237612345678',
            'message_id' => 'aas373737',
            'from' => 'YourCompany',
        ];
        $hCallback = [
            'driver' => [\Camoo\Sms\Database\MariaDB::class, 'getInstance'],
            'bulk_chunk' => 1,
            'db_config' => [
                [
                'db_name'     => 'test',
                'db_user'     => 'test',
                'db_password' => 'secret',
                'db_host'     => 'localhost',
                'table_sms'   => 'my_table',
                ]
            ],
            'variables' => [
            'message'    => 'message',
            'recipient'  => 'to',
            'message_id' => 'message_id',
            'sender'	 => 'from'
            ]
        ];
        Utils::doBulkCallback($hCallback, $data);
    }

    /**
      * @covers \Camoo\Sms\Lib\Utils::doBulkSms
      * @dataProvider doBulkSmsProvider
      */
    public function testdoBulkSms($data, $bulk_chunk)
    {
        $rep = [
            'sms' => [
                'messages' => [
                    [
                        'message-id' => '1763763',
                    ]
                ]
            ]
        ];
        $msgMock = $this->getMockBuilder(Message::class)
            ->setMethods(['send'])
            ->getMock();

        $msgMock->expects($this->any())
            ->method('send')
            ->will($this->returnValue(Utils::normaliseKeys($rep)));
        $hCallback = [
            'driver' => [\Camoo\Sms\Database\MySQL::class, 'getInstance'],
            'bulk_chunk' => $bulk_chunk,
            'db_config' => [
                [
                'db_name'     => 'test',
                'db_user'     => 'test',
                'db_password' => 'secret',
                'db_host'     => 'localhost',
                'table_sms'   => 'my_table',
                ]
            ],
            'variables' => [
            'message'    => 'message',
            'recipient'  => 'to',
            'message_id' => 'message_id',
            'sender'	 => 'from'
            ]
        ];
        $this->assertIsArray(Utils::doBulkSms($data, [], $hCallback, $msgMock));
    }

    public function doBulkSmsProvider()
    {
        return [
            [
                [
                    'message' => 'foo Bar',
                    'to' => [['name' => 'John Doe', 'mobile' => '+237612345678'], ['name' => 'Jeanne Doe', 'mobile' => '+237612345679']],
                    'from' => 'YourCompany',
                ],1
            ],
            [
                [
                    'message' => 'foo Bar',
                    'to' => ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'],
                    'from' => 'YourCompany',
                ],2
            ],
        ];
    }

    public function backgroundProcessData()
    {
        return [
            [[]],
            [['path_to_php' => '/usr/local/bin/php4']],
        ];
    }

    public function isMultiArrayProviderFailure()
    {
        return [
            [[]],
            [['a','b']],
            [['a' => 'b']],
        ];
    }

    public function makeNumberE164FormatData()
    {
        return [
            [[['name' => 'John Doe', 'mobile' => '00237612345678'], ['name' => 'Jeanne Doe', 'mobile' => '+237612345679']]],
            [['+237612345678', '00237612345679', '237612345610', '33689764530', '004917612345671']],
            ['0033689764530']
        ];
    }

    public function senderProvider()
    {
        return [
            ['Your Company'],
            ['Camoo S.A.R.L'],
            ['00237667123456'],
            ['698123456'],
        ];
    }

    public function mtnProviderFailure()
    {
        return [
            [245123456],
            [886123456],
            [667123456],
            [698123456],
            [640123456],
            [641123456],
            [644123456],
        ];
    }

    public function mtnProviderSuccess()
    {
        return [
            [674512345],
            [674612345],
            [674712345],
            [674812345],
            [674912345],
            [675912345],
            [679123456],
        ];
    }

    public function numberProviderFailure()
    {
        return [
            ['0', 'CM'],
            ['', 'FR'],
            ['6', 'CM'],
            ['123', null],
        ];
    }

    public function validNumberProviderSuccess()
    {
        return [
            ['671234567', 'CM',true],
            ['671234567', 'FR', true],
            ['691234567', 'CM', false],
            ['661234567', 'CM',false],
        ];
    }

    public function numberProviderSuccess()
    {
        return [
            ['671234567', 'CM'],
            ['671234567', 'FR'],
            ['691234567', 'CM'],
            ['661234567', 'CM'],
        ];
    }

    public function stripAllTagsDataProvider()
    {
        return [
            ['fooo',1],
            ['[]',true],
            ['<script>alert("Test")</script>',true],
            ['fooo',false],
            ['fooo',0],
        ];
    }

    public function satanizerDataProviderFailure2()
    {
        return [
            [file_get_contents('https://www.cl.cam.ac.uk/~mgk25/ucs/examples/UTF-8-test.txt')],
        ];
    }

    public function satanizerDataProviderFailure1()
    {
        return [
            [ [] ],
            [ new \stdClass ],
        ];
    }
}
