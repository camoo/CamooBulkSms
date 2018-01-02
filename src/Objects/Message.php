<?php
namespace Camoo\Sms\Objects;

/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: src/Objects/Message.php
 * updated: Dec 2017
 * Description: CAMOO SMS message Objects
 *
 * @link http://www.camoo.cm
 */
use Valitron\Validator;
use Camoo\Sms\Exception\CamooSmsException;

final class Message extends Base
{

    /**
     * An unique random ID which is created on Camoo SMS
     * platform and is returned for the created object.
     *
     * @var string
     */
    protected $id;

     /**
     * The sender of the message. This can be a telephone number
     * (including country code) or an alphanumeric string. In case
     * of an alphanumeric string, the maximum length is 11 characters.
     *
     * @var string
     */
    public $from = null;

    /**
     * The content of the SMS message.
     *
     * @var string
     */
    public $message = null;


    /**
     * Recipient that sould receive the sms
     * You can set single recipient (string) or multiple recipients by using array
     *
     * @var string | array
     */
    public $to = null;

    /**
     * The datacoding used, can be text,plain,unicode or auto
     *
     * @var string
     */
    public $datacoding = null;

    /**
     * The type of message. Values can be: sms, binary or flash
     *
     * @var string
     */
    public $type = null;

    public function validatorDefault(Validator $oValidator)
    {
        $oValidator
            ->rule('required', ['from', 'message', 'to']);
        $oValidator
            ->rule('optional', ['type', 'datacoding']);
        $oValidator
            ->rule('in', 'type', ['sms','binary','flash']);
        $oValidator
            ->rule('in', 'datacoding', ['plain','text','unicode', 'auto']);
        $this->isPossibleNumber($oValidator, 'to');
        $this->isValidUTF8Encoded($oValidator, 'from');
        $this->isValidUTF8Encoded($oValidator, 'message');
        return $oValidator;
    }

    public function validatorView(Validator $oValidator)
    {
        $oValidator
            ->rule('required', ['id']);
        $this->notBlankRule($oValidator, 'id');
        return $oValidator;
    }
}
