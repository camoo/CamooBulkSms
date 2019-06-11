<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/vendor/autoload.php';
/**
 * Send BULK sms
 *
 */

$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->from ='YourCompany';
$oMessage->to = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'];
$oMessage->message ='Hello Kmer World! Déjà vu!';
var_dump($oMessage->sendBulk());

// Done!
