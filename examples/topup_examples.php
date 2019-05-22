<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/vendor/autoload.php';
/**
 * @Brief recharge user account
 * Only available for Mobile Money MTN Cameroon Ltd
 */
// Step 1: create balance instance
$oBalance = \Camoo\Sms\Balance::create('YOUR_API_KEY', 'YOUR_API_SECRET');
// Step2: assert needed data
$oBalance->phonenumber = '671234567';
$oBalance->amount = 4000;

// Step3: Add Balance to your account
var_export($oBalance->add());

// output:
/*
stdClass Object
(
    [message] => pending
    [topup] => stdClass Object
        (
            [payment_id] => 40
            [completed] => 0
        )

    [code] => 200
)

// Step4 :
    - Dial *126*1#
    - Choose option to authorize the transaction
    - Enter your MTN Mobile Money PIN
    - Choose the option to approve the Payment
    - Choose option and confirm
 */
