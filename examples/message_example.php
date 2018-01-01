<?php
require_once dirname(dirname(dirname(__DIR__))) . '/autoload.php';
/**
 * @Brief Send a sms
 *
 */
// Step 1: create Message instance

$oMessage = \Camoo\Sms\Message::create();

// Step2: assert data object
$oMessage->from ='YourCompany';
$oMessage->to = '+237612345678';
// OR Send the same message to multi-recipients
// Per request, a max of 50 recipients can be entered.
//$oMessage->to =['+237612345678', '+237612345679', '+237612345610', '+33689764530'];
$oMessage->datacoding ='auto';
$oMessage->type ='sms';
$oMessage->message ='Hello Kmer World! Déjà vu!';

// Step 3: Use send() method to send a message.
var_dump($oMessage->send());
// Done!
