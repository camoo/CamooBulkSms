<?php

namespace CamooSms\Test\TestCase\Database;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Database\AppDb;

/**
 * Class AppDbTest
 * @author CamooSarl
 * @covers Camoo\Sms\Database\AppDb
 */
class AppDbTest extends TestCase
{

    /**
     * @dataProvider mysqlDataProvider
     * @covers Camoo\Sms\Database\AppDb::getInstance
     */
    public function testGetInstance($conf)
    {
        $this->assertInstanceOf(AppDb::class, AppDb::getInstance($conf));
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
