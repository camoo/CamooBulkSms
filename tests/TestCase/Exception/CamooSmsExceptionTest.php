<?php

namespace CamooSms\Test\TestCase\Exception;

use PHPUnit\Framework\TestCase;
use Camoo\Sms\Exception\CamooSmsException;

/**
 * Class CamooSmsExceptionTest
 * @author CamooSarl
 * @covers Camoo\Sms\Exception\CamooSmsException
 */
class CamooSmsExceptionTest extends TestCase
{
	public function testInstance()
	{
		$this->assertInstanceOf(CamooSmsException::class,new CamooSmsException);
	}
}
