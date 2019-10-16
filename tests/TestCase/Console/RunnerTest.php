<?php

namespace CamooSms\Test\TestCase\Console;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Console\Runner;
use \Camoo\Sms\Lib\Utils;

/**
 * Class RunnerTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Console\Runner
 */
class RunnerTest extends TestCase
{
    private $sTmpName;
    private $sTmpFile;


    public function setUp() : void
    {
        $this->sTmpName =  'test' .Utils::randomStr().'.bulk';
        $this->sTmpFile =  \Camoo\Sms\Constants::getSMSPath(). 'tmp/' .$this->sTmpName;
    }

    public function tearDown() : void
    {
        if (file_exists($this->sTmpFile)) {
            unlink($this->sTmpFile);
        }
    }

    /**
     * @covers \Camoo\Sms\Console\Runner::run
     */
    public function testRun()
    {
		$hData = [
			'to' => ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+49176123456'],
			'message' => 'foo bar',
			'from' => 'Foo',
		];
        file_put_contents($this->sTmpFile, json_encode($hData));
        $sPASS = json_encode([[],$this->sTmpName,['api_key' => 'dshsh', 'api_secret' => 'jhghjjkkhgfg']]);
        $argv = [
            'php',
            base64_encode($sPASS)
        ];
        $oRuner = new Runner;
        $this->assertNull($oRuner->run($argv));
    }
}
