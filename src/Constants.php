<?php
declare(strict_types=1);

namespace Camoo\Sms;

/**
 * Class Constants
 *
 */
class Constants
{
    const CLIENT_VERSION = '3.1.5';
    const CLIENT_TIMEOUT = 10; // 10 sec
    const MIN_PHP_VERSION = 70000;
    const DS = '/';
    const END_POINT_URL = 'https://api.camoo.cm';
    const END_POINT_VERSION = 'v1';
    const APP_NAMESPACE = '\\Camoo\\Sms\\';
    const RESOURCE_VIEW = 'view';
    const RESOURCE_BALANCE = 'balance';
    const RESOURCE_ADD = 'topup';
    const RESPONSE_FORMAT = 'json';
    const ERROR_PHP_VERSION = 'Your PHP-Version belongs to a release that is no longer supported. You should upgrade your PHP version as soon as possible, as it may be exposed to unpatched security vulnerabilities';
    const SMS_MAX_RECIPIENTS = 50;
    const CLEAR_OBJECT = [\Camoo\Sms\Base::class, 'clear'];
    const MAP_MOBILE =[\Camoo\Sms\Lib\Utils::class,'mapMobile'];
    const MAP_E164FORMAT =[\Camoo\Sms\Lib\Utils::class,'phoneNumberE164Format'];
    const PERSONLIZE_MSG_KEYS = ['%NAME%'];

    public static $asCredentialKeyWords = ['api_key', 'api_secret'];

    /**
    * @return string
    */
    public static function getPhpVersion() : string
    {
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION); //@codeCoverageIgnore
            define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]); //@codeCoverageIgnore
        }

        if (PHP_VERSION_ID < static::MIN_PHP_VERSION) {
            trigger_error(static::ERROR_PHP_VERSION, E_USER_ERROR);//@codeCoverageIgnore
        }

        return 'PHP/' . PHP_VERSION_ID;
    }

    public static function getSMSPath() : string
    {
        return dirname(__DIR__) .DIRECTORY_SEPARATOR;
    }
}
