<?php

namespace CamooSms\Test\TestCase\Console;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Console\BackgroundProcess;
use Camoo\Sms\Exception\BackgroundProcessException;

/**
 * Class BackgroundProcessTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Console\BackgroundProcess
 */
class BackgroundProcessTest extends TestCase
{

    /**
     * @dataProvider commandDataProvider
     */
    public function testInstance($command)
    {
        $this->assertInstanceOf(BackgroundProcess::class, new BackgroundProcess($command));
    }

    /**
     * @covers \Camoo\Sms\Console\BackgroundProcess::run
     * @depends testInstance
     * @dataProvider commandDataProvider
     */
    public function testRunSuccess($command)
    {
        $run = new BackgroundProcess($command);
        $this->assertIsInt($run->run());
    }

    /**
     * @covers \Camoo\Sms\Console\BackgroundProcess::run
     * @depends testInstance
     */
    public function testRunWithNullThrowsException()
    {
        $this->expectException(BackgroundProcessException::class);
        $run = new BackgroundProcess(null);
        $run->run();
    }

    /**
     * @covers \Camoo\Sms\Console\BackgroundProcess::run
     * @depends testInstance
     */
    public function testRunWithEmptyOSThrowsException()
    {
		$command = 'ls -l /tmp';
        $this->expectException(BackgroundProcessException::class);
        $run = new BackgroundProcess($command);

        $runMock = $this->getMockBuilder(BackgroundProcess::class)
            ->setMethods(['getOS'])
            ->setConstructorArgs([$command])
            ->getMock();

        $runMock->expects($this->once())
            ->method('getOS')
            ->will($this->returnValue(null));
        $runMock->run();
    }


    /**
     * @covers \Camoo\Sms\Console\BackgroundProcess::run
     * @depends testInstance
     * @testWith        ["whoami"]
     */
    public function testRunOther($command)
    {
        $this->expectException(BackgroundProcessException::class);
        $run = new BackgroundProcess($command);

        $runMock = $this->getMockBuilder(BackgroundProcess::class)
            ->setMethods(['getOS'])
            ->setConstructorArgs([$command])
            ->getMock();

        $runMock->expects($this->once())
            ->method('getOS')
            ->will($this->returnValue('Other'));
        $runMock->run();
    }

    /**
     * @covers \Camoo\Sms\Console\BackgroundProcess::run
     * @depends testInstance
     * @testWith        ["whoami"]
     */
    public function testRunWin($command)
    {
        $run = new BackgroundProcess($command);

        $runMock = $this->getMockBuilder(BackgroundProcess::class)
            ->setMethods(['getOS'])
            ->setConstructorArgs([$command])
            ->getMock();

        $runMock->expects($this->once())
            ->method('getOS')
            ->will($this->returnValue('WIN'));
 
        $this->assertIsInt($runMock->run());
    }

    public function commandDataProvider()
    {
        return [
            ['ls -lart'],
            ['whoami'],
        ];
    }
}
