[![N|Solid](https://www.camoo.hosting/img/logos/logoDomain.png)](https://www.camoo.cm/bulk-sms)

PHP SMS API Sending SMS via the **_CAMOO SMS gateway_**

Requirement
-----------

This library needs minimum requirement for doing well on run.

   - [Sign up](https://www.camoo.cm/join) for a free CAMOO SMS account
   - Ask CAMOO Team for new access_key for developers
   - CAMOO SMS API client for PHP requires version 5.6.x and above

## Installation via Composer

Package is available on [Packagist](https://packagist.org/packages/camoo/sms),
you can install it using [Composer](http://getcomposer.org).

```shell
composer require camoo/sms
```
### Or go to

   [Camoo-SMS-API-Latest Release](https://github.com/camoo/sms/releases/tag/v3.1.0)

And download the full version

Quick Examples
--------------

##### Sending a SMS
```php
	$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
	$oMessage->from ='YourCompany';
	$oMessage->to = '+237612345678';
	$oMessage->message ='Hello Kmer World! Déjà vu!';
	var_dump($oMessage->send());
  ```
##### Send the same SMS to many recipients
            
	- Per request, a max of 50 recipients can be entered.
```php
	$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
	$oMessage->from ='YourCompany';
	$oMessage->to =['+237612345678', '+237612345679', '+237612345610', '+33689764530'];
	$oMessage->message ='Hello Kmer World! Déjà vu!';
	var_dump($oMessage->send());
```

##### Sending non customized sender SMS. COSTS: 10FCFA/SMS
```php
    $oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
    $oMessage->from ='WhatEver'; // will be overridden
    $oMessage->to = '+237612345678';
    // This parameter tells our system to use the classic route to send your message.
    $oMessage->route ='classic'; 
    $oMessage->message ='Hello Kmer World! Déjà vu!';
    var_dump($oMessage->send());
```

##### Sending an encrypted SMS
  	Encrypt message using GPG before sending, ensure an end to end ecryption between your server and ours
```php
	$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
	$oMessage->from ='YourCompany';
	$oMessage->to = '+237612345678';
	$oMessage->message ='Hello Kmer World! Déjà vu!';
        $oMessage->encrypt = true;
	var_dump($oMessage->send());
  ```

##### Sending Bulk SMS from your Script
It is obvious that sending bulk data to any system is a problem! Therefore you should check our recommendation for the best approach
   - (_**[See example for bulk sms](https://github.com/camoo/sms/wiki/How-to-send-Bulk-SMS-from-your-script#send-sms-sequentially)**_)

Resources
---------

  * [Documentation](https://github.com/camoo/sms/wiki)
  * [Report issues](https://github.com/camoo/sms/issues)
