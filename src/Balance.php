<?php
namespace Camoo\Sms;

/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: src/Balance.php
 * Updated: Jan. 2018
 * Created by: Camoo Sarl (sms@camoo.sarl)
 * Description: CAMOO SMS LIB
 *
 * @link http://www.camoo.cm
 */

/**
 * Class Camoo\Sms\Balance
 * Get or add balance to your account
 *
 */
use Camoo\Sms\Exception\CamooSmsException;

class Balance extends Base
{
    const RESOURCE_BALANCE = 'balance';
    const RESOURCE_ADD = 'topup';

    /**
    * read the current user balance
    * @return mixed Balance
    */
    public function get()
    {
        try {
            $this->setResourceName(static::RESOURCE_BALANCE);
            $oHttpClient = new HttpClient($this->getEndPointUrl(), $this->getCredentials());
            return $this->decode($oHttpClient->performRequest('GET'));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('Balance Request can not be performed!');
        }
    }

    /**
    * Initiate a topup to recharge a user account
    * Only available for MTN Mobile Money Cameroon
    *
    * @return mixed Trx
    */
    public function add()
    {
        try {
            $this->setResourceName(static::RESOURCE_ADD);
            $oHttpClient = new HttpClient($this->getEndPointUrl(), $this->getCredentials());
            return $this->decode($oHttpClient->performRequest('POST', $this->getData()));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('Topup Request can not be performed!');
        }
    }
}
