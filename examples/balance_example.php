<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/vendor/autoload.php';
/**
 * @Brief read current balance
 *
 */
// Step 1: create balance instance
$oBalance = \Camoo\Sms\Balance::create('YOUR_API_KEY', 'YOUR_API_SECRET');

// Step2: retrieve your current balance
var_export($oBalance->get());

// output:
/*
stdClass Object
(
    [message] => OK
    [balance] => stdClass Object
        (
            [balance] => 910
            [currency] => XAF
        )

)*/
