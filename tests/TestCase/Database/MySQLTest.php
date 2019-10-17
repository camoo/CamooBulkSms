<?php

namespace CamooSms\Test\TestCase\Database;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Database\MySQL;

/**
 * Class MySQLTest
 * @author CamooSarl
 * @covers Camoo\Sms\Database\MySQL
 */
class MySQLTest extends TestCase
{

    /**
     * @dataProvider mysqlDataProvider
     * @covers Camoo\Sms\Database\MySQL::getInstance
     */
    public function testGetInstance($conf)
    {
        $this->assertInstanceOf(MySQL::class, MySQL::getInstance());
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::getDB
     * @dataProvider mysqlDataProvider
     */
    public function testGetDb($conf)
    {
        $this->assertInstanceOf(MySQL::class, MySQL::getInstance($conf)->getDB());
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::close
     * @dataProvider mysqlDataProvider
     */
    public function testClose($conf)
    {
        $this->assertTrue(MySQL::getInstance($conf)->getDB()->close());
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::getDB
     * @dataProvider mysqlDataProviderFailure
     */
    public function testGetDbFailure($conf)
    {
        $this->assertFalse(MySQL::getInstance($conf)->getDB());
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::query
     * @dataProvider mysqlDataProvider
     */
    public function testGetQueury($conf)
    {
        $this->assertIsObject(MySQL::getInstance($conf)->getDB()->query('show tables'));
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::query
     * @dataProvider mysqlDataProvider
     */
    public function testGetQueuryFailure($conf)
    {
        $this->assertFalse(MySQL::getInstance($conf)->getDB()->query('desc table users'));
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::insert
     * @dataProvider mysqlDataProvider
     */
    public function testGetInsertSuccess($conf)
    {
        $table = 'messages';

        $variables = [
            'message'    => 'Foo Bar',
            'recipient'  => '33612345678',
            'message_id' => '12233638',
            'sender'	 => 'Yourcompany'
        ];
        $queryMock = $this->getMockBuilder(MySQL::class)
            ->setMethods(['query','escape_string'])
            ->getMock();

        $queryMock->expects($this->any())
            ->method('escape_string')
            ->will($this->returnValue('a mocked string'));

        $queryMock->expects($this->once())
            ->method('query')
            ->will($this->returnValue(true));
        $this->assertTrue($queryMock->insert($table, $variables));
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::insert
     * @dataProvider insertDataProviderFailure
     */
    public function testGetInsertFailure($conf, $variables)
    {
        $table = 'messages';

        $queryMock = $this->getMockBuilder(MySQL::class)
            ->setMethods(['query','escape_string'])
            ->getMock();

        $queryMock->expects($this->any())
            ->method('escape_string')
            ->will($this->returnValue('a mocked string'));

        $queryMock->expects($this->any())
            ->method('query')
            ->will($this->returnValue(false));
        $this->assertFalse($queryMock->insert($table, $variables));
    }

    /**
     * @covers Camoo\Sms\Database\MySQL::escape_string
     * @dataProvider stringEscapDataProvider
     */
    public function testEscapeStr($conf, $str)
    {
        $this->assertIsString(MySQL::getInstance($conf)->getDB()->escape_string($str));
    }

    public function insertDataProviderFailure()
    {
        return [
            [
                [
                'db_name'     => 'cm_test',
                'db_user'     => 'travis',
                'db_password' => '',
                'db_host'     => '127.0.0.1',
                'table_sms'   => 'my_table',
                ], []
            ],
            [
                [
                'db_name'     => 'cm_test',
                'db_user'     => 'travis',
                'db_password' => '',
                'db_host'     => '127.0.0.1',
                'table_sms'   => 'my_table',
                ],
                [
                'message'    => 'Foo Bar',
                'recipient'  => '33612345678',
                'message_id' => '12233638',
                'sender'	 => 'Yourcompany'
                ]
            ],

        ];
    }

    public function stringEscapDataProvider()
    {
        return [
            [
                [
                'db_name'     => 'cm_test',
                'db_user'     => 'travis',
                'db_password' => '',
                'db_host'     => '127.0.0.1',
                'table_sms'   => 'my_table',
                ], '"SELECT 1=1;"'
            ],
            [
                [
                'db_name'     => 'cm_test',
                'db_user'     => 'travis',
                'db_password' => '',
                'db_host'     => '127.0.0.1',
                'table_sms'   => 'my_table',
                ], '"some good string"'
            ],

        ];
    }

    public function mysqlDataProvider()
    {
        return [
            [
                [
                'db_name'     => 'cm_test',
                'db_user'     => 'travis',
                'db_password' => '',
                'db_host'     => '127.0.0.1',
                'table_sms'   => 'my_table',
                ]
            ]
        ];
    }

    public function mysqlDataProviderFailure()
    {
        return [
            [
                [
                'db_name'     => 'cm_test',
                'db_user'     => 'root',
                'db_password' => 'secret',
                'db_host'     => 'localhost',
                'table_sms'   => 'my_table',
                ]
            ]
        ];
    }
}
