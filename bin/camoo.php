#!/usr/bin/php -q
<?php

if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    trigger_error('The CAMOO SMS Library requires PHP version 7.0.0 or higher', E_USER_ERROR);
    exit;
}

try {
    if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
        // Downloaded installation
        require_once dirname(__DIR__) . '/vendor/autoload.php';
    } else {
        // Composer installation
        require_once dirname(dirname(dirname(__DIR__))) . '/autoload.php';
    }
} catch (\Exception $err) {
    trigger_error($err->getMessage(), E_USER_ERROR);
    exit;
}
use Camoo\Sms\Console\Runner;

$oRunner = new Runner();
exit($oRunner->run($argv));
