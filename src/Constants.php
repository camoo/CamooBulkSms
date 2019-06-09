<?php
declare(strict_types=1);
namespace Camoo\Sms;

/**
 * Class Constants
 *
 */
class Constants
{
    public const CLIENT_VERSION = '3.1.1';
    public const CLIENT_TIMEOUT = 10; // 10 sec
    public const MIN_PHP_VERSION = 70100;
    public const DS = '/';
    public const END_POINT_URL = 'https://api.camoo.cm';
    public const END_POINT_VERSION = 'v1';
    public const APP_NAMESPACE = '\\Camoo\\Sms\\';
    public const RESOURCE_VIEW = 'view';
    public const RESOURCE_BALANCE = 'balance';
    public const RESOURCE_ADD = 'topup';
    public const RESPONSE_FORMAT = 'json';
    public const ERROR_PHP_VERSION = 'Your PHP-Version belongs to a release that is no longer supported. You should upgrade your PHP version as soon as possible, as it may be exposed to unpatched security vulnerabilities';
    public const SMS_MAX_RECIPIENTS = 50;

    public static $asCredentialKeyWords = ['api_key', 'api_secret'];

    /**
    * @return string
    */
    public static function getPhpVersion() : string
    {
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]);
        }

        if (PHP_VERSION_ID < static::MIN_PHP_VERSION) {
            trigger_error(static::ERROR_PHP_VERSION, E_USER_ERROR);
        }

        return 'PHP/' . PHP_VERSION_ID;
    }
}
