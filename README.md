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

   [Camoo-SMS-API-Latest Release](https://gitlab.com/camoo/sms/releases/tag/v3.0.5)

And download the full version

###### Edit sr/config/app.php
```php
	return [
	   'local_login' => true,
	   'App' => [
	      'api_key' => '592595095', // add your api_key
	      'api_secret' => '79b89479847b9798479494984', // add your api_secret
	      'response_format' => 'json',// json or xml
	    ]
	];
  ```

###### OPTION `local_login`,
	`true` : The API should use the credentials from the file app.php
	`false` : You want to pass your credentials on the fly for each request

Quick Examples
--------------

##### Sending a SMS
```php
	#$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET'); // in case local_login is false or config/app is missing
	$oMessage = \Camoo\Sms\Message::create();
	$oMessage->from ='YourCompany';
	$oMessage->to = '+237612345678';
	$oMessage->datacoding ='auto'; // possible values: plain,text,unicode or auto
	$oMessage->type ='sms';
	$oMessage->message ='Hello Kmer World! Déjà vu!';

	var_dump($oMessage->send());
  ```
##### Send the same SMS to many recipients
            
	- Per request, a max of 50 recipients can be entered.
```php
	#$oMessage = \Camoo\Sms\Message::create('YOUR_API_KEY', 'YOUR_API_SECRET'); // in case local_login is false or config/app.php is missing
	$oMessage = \Camoo\Sms\Message:create();
	$oMessage->from ='YourCompany';
	$oMessage->to =['+237612345678', '+237612345679', '+237612345610', '+33689764530'];
	$oMessage->datacoding ='auto';
	$oMessage->type ='sms';
	$oMessage->message ='Hello Kmer World! Déjà vu!';

	var_dump($oMessage->send());
```

Resources
---------

  * [Documentation](https://gitlab.com/camoo/sms/wikis)
  * [Report issues](https://gitlab.com/camoo/sms/issues)
