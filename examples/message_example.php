<?php
require_once dirname(dirname(dirname(__DIR__))) . '/autoload.php';
/**
 * @Brief Send a sms
 *
 */
// Step 1: create Message instance

$oSMS = \Camoo\Sms\Message::create();

// Step2: assert data object
$oSMS->from ='YourCompany';
$oSMS->to = '+237612345678';
// OR Send the same message to multi-recipients
// Per request, a max of 50 recipients can be entered.
//$oSMS->to =['+237612345678', '+237612345679', '+237612345610', '+33689764530'];
$oSMS->datacoding ='auto';
$oSMS->type ='sms';
$oSMS->message ='Hello Kmer World! Déjà vu!';

// Step 3: Use send() method to send a message.
var_dump($oSMS->send());
// Done!
