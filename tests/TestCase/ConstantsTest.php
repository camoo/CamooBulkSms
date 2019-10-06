<?php

namespace CamooSms\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Constants;

/**
 * Class ConstantsTest
 * @author CamooSarl
 * @covers \Camoo\Sms\Constants
 */
class ConstantsTest extends TestCase
{
    public function testgetPhpVersion()
    {
        $this->assertStringContainsString('PHP/', Constants::getPhpVersion());
    }

    public function testgetSMSPath()
    {
        $this->assertSame(dirname(dirname(__DIR__)) .DIRECTORY_SEPARATOR, Constants::getSMSPath());
    }
}
