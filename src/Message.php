<?php
namespace Camoo\Sms;

/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: src/Message.php
 * Updated: Dec. 2017
 * Created by: Camoo Sarl (sms@camoo.sarl)
 * Description: CAMOO SMS LIB
 *
 * @link http://www.camoo.cm
 */

/**
 * Class Camoo\Sms\Message handles the methods and properties of sending an SMS message.
 *
 */
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Objects;

class Message extends Base
{
    public static function create($dataObject = null)
    {
        static::$dataObject = Objects\Message::create();
        parent::create(static::$dataObject);
        return new self;
    }

    /**
     * Send Message
     *
     */
    public function send()
    {
        $hPost = $this->getData();
        // Making sure strings are UTF-8 encoded
        if (!is_numeric($hPost['from']) && !mb_check_encoding($hPost['from'], 'UTF-8')) {
            trigger_error('from needs to be a valid UTF-8 encoded string');
            return false;
        }

        if (!mb_check_encoding($hPost['message'], 'UTF-8')) {
            trigger_error('message needs to be a valid UTF-8 encoded string');
            return false;
        }
        return $this->sendSmsRequest($hPost);
    }

    /**
     * Prepare and send a new message.
     */
    private function sendSmsRequest($data)
    {
        try {
            $oHttpClient = new HttpClient($this->getEndPointUrl(), static::$hCredentials);
            return $this->decode($oHttpClient->performRequest('POST', $data));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('SMS Request can not be performed!');
        }
    }
   
    /**
    * Read a sent message by Id
    *
    * @return mixed Message
    */
    public function view()
    {
        try {
            $this->setResourceName('view');
            $oHttpClient = new HttpClient($this->getEndPointUrl(), static::$hCredentials);
            return $this->decode($oHttpClient->performRequest('GET', $this->getData()));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('View Request can not be performed!');
        }
    }
}
