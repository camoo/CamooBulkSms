<?php
    require_once dirname(dirname(dirname(__DIR__))) . '/autoload.php';
    /**
     * @Brief View Message by message-id
     */
    // Step 1: create Message instance
    $oSMS = \Camoo\Sms\Message::create();
    $oSMS->id = '686874387367648440';
    var_export($oSMS->view());
