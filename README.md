[![N|Solid](https://www.camoo.cm/img/icon/camoo_logo_thom1.png)](https://www.camoo.cm/bulk-sms)

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
## Or go to

   [Camoo-SMS-API-Latest Release](https://github.com/camoo/sms/releases/tag/v3.0.2)

And download the full version

###### Edit sr/config/app.php
```php
	return [
	   'App' => [
	    'api_key' => '592595095', // add your api_key
	    'api_secret' => '79b89479847b9798479494984', // add your api_secret
	    'response_format' => 'json',// json or xml
	    ]
	];
  ```

Quick Examples
--------------

#### Sending a SMS
```php
	$oMessage = \Camoo\Sms\Message::create();
	$oMessage->from ='YourCompany';
	$oMessage->to = '+237612345678';
	$oMessage->datacoding ='auto'; // possible values: plain,text,unicode or auto
	$oMessage->type ='sms';
	$oMessage->message ='Hello Kmer World! Déjà vu!';

	var_dump($oMessage->send());
  ```
#### Send the same SMS to many recipients
            
            - Per request, a max of 50 recipients can be entered.
```php
	$oMessage = \Camoo\Sms\Message:create();
	$oMessage->from ='YourCompany';
	$oMessage->to =['+237612345678', '+237612345679', '+237612345610', '+33689764530'];
	$oMessage->datacoding ='auto';
	$oMessage->type ='sms';
	$oMessage->message ='Hello Kmer World! Déjà vu!';

	var_dump($oMessage->send());
```
Most Frequent Issues
--------------------

Sending a message returns false.

	This is usually due to your webserver unable to send a request to CAMOO. Make sure the following are met:

  		Either CURL is enabled for your PHP installation or the PHP option 'allow_url_fopen' is set to 1 (default).
		You have no firewalls blocking access on port 443.
   
Your message appears to have been sent but you do not recieve it.

    Run the example.php file included. This will show any errors that are returned from CAMOO.
    
Handle a status rapport
------------------------

Status rapports are requests that are sent to your platform through a GET request. The requests holds information about the status of a message that you have sent through our API. status rapports are only provided for messages that have configured their status rapport url.

ATTRIBUTES


| Attribute     | Type          | Description  |
| ------------- |:-------------:|:-----:|
| id            | string        | An unique random ID which is created on the CAMOO platform and is returned upon creation of the object. |
| recipient     | string        | The recipient where this status rapport applies to. |
| status        | string        | The status of the message sent to the recipient. Possible values: `scheduled`, `sent`, `buffered`, `delivered`, `expired`, `anddelivery_failed` |
| statusDatetime| datetime      | The time of this status in RFC3339 format date('Y-m-d H:i:s') |


REQUEST

    GET http://your-own.url/script?id=b9389ur787874487486844&recipient=237612345678&status=delivered&statusDatetime=2016-11-05 13:35:35
    
RESPONSE

    200 OK
 
 Your platform should respond with a 200 OK HTTP header.
