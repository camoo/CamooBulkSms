<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/vendor/autoload.php';
/**
 * @Brief View Message by message-id
 */
// Step 1: create Message instance
$oSMS = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oSMS->id = '686874387367648440';
var_export($oSMS->view());
