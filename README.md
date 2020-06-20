<p align="center">
  <a href="https://www.camoo.cm/bulk-sms" target="_blank" >
    <img alt="CamooSms" src="https://www.camoo.hosting/img/logos/logoDomain.png"/>
  </a>
</p>
<p align="center">
	PHP SMS API Sending SMS via the <strong><em>CAMOO SMS gateway</em></strong>
</p>
<p align="center">
    <a href="https://travis-ci.com/camoo/sms" target="_blank">
        <img alt="Build Status" src="https://travis-ci.com/camoo/sms.svg?branch=master">
    </a>
	<a href="https://codecov.io/gh/camoo/sms">
  		<img src="https://codecov.io/gh/camoo/sms/branch/master/graph/badge.svg" />
	</a>
</p>

Requirement
-----------

This library needs minimum requirement for doing well on run.

   - [Sign up](https://www.camoo.cm/join) for a free CAMOO SMS account
   - Ask CAMOO Team for new access_key for developers
   - CAMOO SMS API client for PHP requires version 7.0.x and above

## Installation via Composer

Package is available on [Packagist](https://packagist.org/packages/camoo/sms),
you can install it using [Composer](http://getcomposer.org).

```shell
composer require camoo/sms
```
### Or go to

   [Camoo-SMS-API-Latest Release](https://github.com/camoo/sms/releases/tag/v3.1.5)

And download the full version

If you want to install a legacy version running with `PHP5.6`
Run composer with the command below
```shell
composer require camoo/sms "3.0.*"
```
Or Download it from [Camoo-SMS-API-Legacy](https://github.com/camoo/sms/releases/tag/v3.0.6)

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

##### Sending non customized sender SMS.
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

##### Sending BULK SMS
Send Bulk SMS in background. This call requires `shell_exec` to be enabled
```php
	$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
	$oMessage->from ='YourCompany';
	$oMessage->to = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'];
	$oMessage->message ='Hello Kmer World! Déjà vu!';
	var_dump($oMessage->sendBulk());
  ```
##### Sending Personalized BULK SMS
Send Bulk SMS in background. This call requires `shell_exec` to be enabled
You  should use the placeholder `%NAME%` in your message and the property `to` should be an associative array containing `name` and `mobile` for each element. See the example below
```php
	$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
	$oMessage->from ='YourCompany';
	$oMessage->to = [['name' => 'John Doe', 'mobile' => '+237612345678'], ['name' => 'Jeanne Doe', 'mobile' => '+237612345679'], ['...']];
	$oMessage->message ='Hello %NAME% Kmer World! Déjà vu!';
	var_dump($oMessage->sendBulk());
  ```
##### Sending Bulk SMS from your Script
It is obvious that sending bulk data to any system is a problem! Therefore you should check our recommendation for the best approach
   - (_**[See example for bulk sms](https://github.com/camoo/sms/wiki/How-to-send-Bulk-SMS-from-your-script#send-sms-sequentially)**_)

WordPress Plugin
----------------
If you are looking for a powerful WordPress plugin to send SMS, then download our [wp-camoo-sms](https://github.com/camoo/wp-camoo-sms)

Resources
---------

  * [Documentation](https://github.com/camoo/sms/wiki)
  * [Report issues](https://github.com/camoo/sms/issues)
