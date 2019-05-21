<?php
declare(strict_types=1);
namespace Camoo\Sms;

/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: src/Message.php
 * Updated: Jan. 2018
 * Created by: Camoo Sarl (sms@camoo.sarl)
 * Description: CAMOO SMS LIB
 *
 * @link http://www.camoo.cm
 */

/**
 * Class Camoo\Sms\Message handles the methods and properties of sending a SMS message.
 *
 */
use Camoo\Sms\Exception\CamooSmsException;

class Message extends Base
{

    /**
     * Send Message
     *
     * @throws Exception\CamooSmsException
     */
    public function send()
    {
        try {
            return $this->execRequest(HttpClient::POST_REQUEST);
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('SMS Request can not be performed!');
        }
    }
   
     /**
     * view a sent message
     *
     * @throws Exception\CamooSmsException
     * @return mixed Message
     */
    public function view()
    {
        try {
            $this->setResourceName(Constants::RESOURCE_VIEW);
            return $this->execRequest(HttpClient::GET_REQUEST, true, Constants::RESOURCE_VIEW);
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('View Request can not be performed!');
        }
    }
}
