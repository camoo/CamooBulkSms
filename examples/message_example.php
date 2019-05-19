<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
/**
 * @Brief Send a sms
 *
 */
// Step 1: create Message instance

$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');

// Step2: assert data object
$oMessage->from ='YourCompany'; // $oMessage->from '+237612345679';
$oMessage->to = '+237612345678';
// OR Send the same message to multi-recipients
// Per request, a max of 50 recipients can be entered.
//$oMessage->to =['+237612345678', '+237612345679', '+237612345610', '+33689764530'];
$oMessage->datacoding ='auto';
$oMessage->encrypt = true; //Encrypt message before sending.
$oMessage->message ='Hello Kmer World! Déjà vu!';
// Step 3: Use send() method to send a message.
var_dump($oMessage->send());

##### Example for sending classic SMS 10FCFA/SMS ########
# When sending classic SMS you can't customize the sender. This type is only availble for cameroonian phone numbers

$oMessage->from ='WhatEver'; // will be overridden
// OR Send the same message to multi-recipients
// Per request, a max of 50 recipients can be entered.
//$oMessage->to =['+237612345678', '+237612345679', '+237612345610', '+33689764530'];
$oMessage->datacoding ='auto';
$oMessage->route ='classic';  // This parameter tells our system to use the classic route to send your message.
$oMessage->message ='Hello Kmer World! Déjà vu!';
var_dump($oMessage->send());

################ Classic SMS END #####################
// Done!
