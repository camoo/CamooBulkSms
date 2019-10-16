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
        $this->assertInstanceOf(MySQL::class, MySQL::getInstance($conf)->getDB());
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
                'db_password' => '',
                'db_host'     => 'localhost',
                'table_sms'   => 'my_table',
                ]
            ]
        ];
    }
}
