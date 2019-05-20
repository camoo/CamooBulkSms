<?php
declare(strict_types=1);
namespace Camoo\Sms\Objects;

use Valitron\Validator;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Lib\Utils;

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

    public function set(string $sProperty, $value, $oClass = null)
    {
        if ($oClass === null) {
            return;
        }
        if (!property_exists($oClass, $sProperty)) {
            throw new CamooSmsException([$sProperty => 'is not allowed!']);
        }
        if ($sProperty === 'from') {
            $value = Utils::clearSender($value);
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
        if (array_key_exists('route', $hPayload) && $hPayload['route'] === 'classic' && $oClass instanceof \Camoo\Sms\Objects\Message && array_key_exists('to', $hPayload)) {
            $asTo = explode(',', $hPayload['to']);
            foreach ($asTo as $sTo) {
                if (!Utils::isValidPhoneNumber($sTo)) {
                    throw new CamooSmsException([$sTo => 'does not seems to be a cameroonian phone number!']);
                }
            }
        }

        return array_filter($hPayload);
    }

    public function isMTNCameroon(&$oValidator, string $sParam) : bool
    {
        $oValidator
            ->rule(function ($field, $value, $params, $fields) {
                if (is_null($value) || empty($value)) {
                    return false;
                } elseif (is_string($value) && trim($value) === '') {
                    return false;
                }
                    return Utils::isCmMTN($value);
            }, $sParam)->message("{field} is not carried by MTN Cameroon");
    }

    public function isValidUTF8Encoded(&$oValidator, string $sParam)
    {
        $oValidator
            ->rule(function ($field, $value, $params, $fields) {
                if (is_null($value) || empty($value)) {
                    return false;
                }
                return mb_check_encoding($value, 'UTF-8');
            }, $sParam)->message("{field} needs to be a valid UTF-8 encoded string");
        return true;
    }

    public function notBlankRule(&$oValidator, string $sParam)
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

    public function isPossibleNumber(&$oValidator, string $sParam)
    {
        $oValidator
            ->rule(function ($field, $value, $params, $fields) {
                $asTo = explode(',', $value);
                if (empty($value)) {
                    return false;
                }

                foreach ($asTo as $sTo) {
                    $xTel = preg_replace('/[^\dxX]/', '', $sTo);
                    $xTel = ltrim($xTel, '0');
                    if (!is_numeric($xTel) || mb_strlen($xTel) <= 10 || mb_strlen($xTel) > 15) {
                        return false;
                    }
                }
                    return true;
            }, $sParam)->message("{field} no (correct) phone number found!");
    }
}
