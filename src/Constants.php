<?php
namespace Camoo\Sms;

/**
 * Class Constants
 *
 */
class Constants
{
    const CLIENT_VERSION = '3.0.4';
    const CLIENT_TIMEOUT = 10; // 10 sec
    const MIN_PHP_VERSION = 50600;
    const DS = '/';
    const END_POINT_URL = 'https://api.camoo.cm';
    const APP_NAMESPACE = '\\Camoo\\Sms\\';
    const RESOURCE_VIEW = 'view';
    const RESOURCE_BALANCE = 'balance';
    const RESOURCE_ADD = 'topup';
    const ERROR_PHP_VERSION = 'Your PHP-Version belongs to a release that is no longer supported. You should upgrade your PHP version as soon as possible, as it may be exposed to unpatched security vulnerabilities';

    public static $asCredentialKeyWords = ['api_key', 'api_secret'];

     /**
     * @return string
     */
    public static function getPhpVersion()
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
