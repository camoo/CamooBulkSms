<?php

namespace CamooSms\Test\TestCase\Database;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Database\AppDb;
use \Doctrine\DBAL\Query\QueryBuilder;
use Camoo\Sms\Exception\CamooSmsException;

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

    /**
     * @covers Camoo\Sms\Database\AppDb::close
     * @dataProvider mysqlDataProvider
     */
    public function testClose($conf)
    {
        $this->assertNull(AppDb::getInstance($conf)->close());
    }

    /**
     * @covers Camoo\Sms\Database\AppDb::query
     * @dataProvider mysqlDataProvider
     */
    public function testGetQuery($conf)
    {
        $this->assertInstanceOf(QueryBuilder::class, AppDb::getInstance($conf)->query());
    }

    /**
     * @covers Camoo\Sms\Database\AppDb::insert
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

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
        ->disableOriginalConstructor()
            ->setMethods(['insert','values', 'execute'])
        ->getMock();

        $queryMock = $this->getMockBuilder(AppDb::class)
            ->setMethods(['query'])
            ->getMock();

        $queryMock->expects($this->once())
            ->method('query')
            ->will($this->returnValue($queryBuilderMock));


        $queryBuilderMock->expects($this->once())
            ->method('insert')
            ->will($this->returnValue($queryBuilderMock));


        $queryBuilderMock->expects($this->once())
            ->method('values')
            ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));

        $this->assertTrue($queryMock->insert($table, $variables));
    }

    /**
     * @covers Camoo\Sms\Database\AppDb::insert
     * @dataProvider insertDataProviderFailure
     */
    public function testGetInsertFailure($conf, $variables)
    {
        $table = 'messages';

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
        ->disableOriginalConstructor()
            ->setMethods(['insert','values', 'execute'])
        ->getMock();

        $queryMock = $this->getMockBuilder(AppDb::class)
            ->setMethods(['query'])
            ->getMock();

        $queryMock->expects($this->any())
            ->method('query')
            ->will($this->returnValue($queryBuilderMock));


        $queryBuilderMock->expects($this->any())
            ->method('insert')
            ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->any())
            ->method('values')
            ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(false));

        $this->assertFalse($queryMock->insert($table, $variables));
    }

    /**
     * @covers Camoo\Sms\Database\AppDb::insert
     * @dataProvider mysqlDataProvider
     */
    public function testInsertToSQL($conf)
    {
        $table = 'messages';

        $variables = [
            'message'    => 'Foo Bar',
            'recipient'  => '33612345678',
            'message_id' => '12233638',
            'sender'	 => 'Yourcompany'
        ];

        $this->assertStringContainsString('INSERT INTO', AppDb::getInstance($conf)->insertToSQL($table, $variables));
    }

    /**
     * @covers Camoo\Sms\Database\AppDb::getInstance
     * @dataProvider mysqlDataProvider
     */
    public function testMissingMethode($conf)
    {
        $this->expectException(CamooSmsException::class);
        AppDb::getInstance($conf)->insertToSQLs('foo');
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
