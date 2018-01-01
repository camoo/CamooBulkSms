<?php
namespace Camoo\Sms\Objects;

/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: src/Objects/Balance.php
 * updated: Dec 2017
 * Description: CAMOO SMS message Objects
 *
 * @link http://www.camoo.cm
 */
use Valitron\Validator;
use Camoo\Sms\Exception\CamooSmsException;

final class Balance extends Base
{

    public static function create()
    {
        return new self;
    }

    /**
     * Phonenumber.
     * Only available for MTN Mobile Money Cameroon
     *
     * @var string
     */
    protected $phonenumber;

     /**
     * amount that should be recharged
     *
     * @var string
     */
    public $amount = null;

    protected function __clone()
    {
    }

    /**
     * constructor
     *
     */
    public function __construct()
    {
    }

    public function validatorDefault(Validator $oValidator)
    {
        $oValidator
            ->rule('required', ['phonenumber', 'amount']);
        $oValidator
            ->rule('integer', 'amount');
        $this->isMTNCameroon($oValidator, 'phonenumber');
        return $oValidator;
    }
}
