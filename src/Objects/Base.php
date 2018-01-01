<?php
namespace Camoo\Sms\Objects;

use Valitron\Validator;
use Camoo\Sms\Exception\CamooSmsException;

/**
 * Class Objects\Base
 *
 */
class Base
{
    protected static $_create = null;

    public static function create()
    {
        if (is_null(static::$_create)) {
            static::$_create = new self;
        }
        return static::$_create;
    }

    public function set($sProperty, $value, $oClass = null)
    {
        if (is_null($oClass)) {
            return;
        }
        if (!property_exists($oClass, $sProperty)) {
            throw new CamooSmsException([$sProperty => 'is not allowed!']);
        }
        if ($sProperty === 'from') {
            $value = $this->clearOriginator($value);
        }
        if ($sProperty === 'to') {
            $value = !is_array($value)? $value : implode(',', $value);
        }

        $oClass->$sProperty = $value;
    }

    public function get($oClass, $validator = 'default')
    {
        if (empty($oClass)) {
            return [];
        }
        $hPayload = get_object_vars($oClass);
        if (method_exists($oClass, 'validator' .ucfirst($validator))) {
            $sValidator = 'validator' .ucfirst($validator);
            $oValidator = $oClass->$sValidator(new Validator($hPayload));
            if ($oValidator->validate() === false) {
                throw new CamooSmsException($oValidator->errors());
            }
        }
        return array_filter($hPayload);
    }

    public function isMTNCameroon(&$oValidator, $sParam)
    {
        $oValidator
            ->rule(function ($field, $value, $params, $fields) {
                if (is_null($value) || empty($value)) {
                    return false;
                } elseif (is_string($value) && trim($value) === '') {
                    return false;
                }
                    return $this->isCmMTN($value);
            }, $sParam)->message("{field} is not carried by MTN Cameroon");
    }

    public function notBlankRule(&$oValidator, $sParam)
    {
        $oValidator
            ->rule(function ($field, $value, $params, $fields) {
                if (is_null($value) || empty($value)) {
                    return false;
                } elseif (is_string($value) && trim($value) === '') {
                    return false;
                }
                    return true;
            }, $sParam)->message("{field} can not be blank/empty...");
    }

    /**
     * @Brief make clear originator
     *
     * If the originator ('from' field) is invalid, some networks may reject the network
     * whilst stinging you with the financial cost! While this cannot correct them, it
     * will try its best to correctly format them.
     */
    private function clearOriginator($inp)
    {
        // Remove any invalid characters
        $ret = preg_replace('/[^a-zA-Z0-9]/', '', (string)$inp);

        if (preg_match('/[a-zA-Z]/', $inp)) {
            // Alphanumeric format so make sure it's < 11 chars
            $ret = substr($ret, 0, 11);
        } else {
            // Numerical, remove any prepending '00'
            if (substr($ret, 0, 2) == '00') {
                $ret = substr($ret, 2);
                $ret = substr($ret, 0, 15);
            }
        }

        return (string)$ret;
    }

    private function isCmMobile($xTel)
    {
        return (boolean) preg_match('/(?=^6).{9}$/', preg_replace('/[^\dxX]/', '', $xTel));
    }

    private function isCmMTN($xTel)
    {
        if ($this->isCmMobile($xTel)) {
            return (boolean) preg_match('/^(67|650|651|652|653|654|683|680|681|682)\s*/', $xTel);
        }
        return false;
    }
}
