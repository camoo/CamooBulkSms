<?php
declare(strict_types=1);
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

    /**
    * read the current user balance
    *
    * @throws Exception\CamooSmsException
    * @return mixed Balance
    */
    public function get()
    {
        try {
            $this->setResourceName(Constants::RESOURCE_BALANCE);
            return $this->execRequest(HttpClient::GET_REQUEST, false);
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('Balance Request can not be performed!');
        }
    }

    /**
    * Initiate a topup to recharge a user account
    * Only available for MTN Mobile Money Cameroon
    *
    * @throws Exception\CamooSmsException
    * @return mixed Trx
    */
    public function add()
    {
        try {
            $this->setResourceName(Constants::RESOURCE_ADD);
            return $this->execRequest(HttpClient::POST_REQUEST);
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('Topup Request can not be performed!');
        }
    }
}
