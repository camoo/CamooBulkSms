<?php
   require_once dirname(dirname(dirname(__DIR__))) . '/autoload.php';
    /**
     * @Brief read current balance
     *
     */
    // Step 1: create balance instance
    $oBalance = \Camoo\Sms\Balance::create();

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
