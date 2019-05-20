<?php
declare(strict_types=1);
namespace Camoo\Sms\Lib;

use \libphonenumber\PhoneNumberUtil;

class Utils
{

    public static function phoneUtil()
    {
        return PhoneNumberUtil::getInstance();
    }

    public static function getNumberProto($xTel, $sCcode = null)
    {
        if (isset($xTel) && Validation::notBlank($xTel)) {
            try {
                return self::phoneUtil()->parse($xTel, $sCcode);
            } catch (\libphonenumber\NumberParseException $e) {
                return null;
            }
        }
        return null;
    }


    public static function isValidPhoneNumber(string $xTel, string $sCcode = 'CM') : bool
    {
        return null !== ($oNumberProto=self::getNumberProto($xTel, $sCcode)) && self::phoneUtil()->isValidNumber($oNumberProto) && !empty(self::phoneUtil()->getNumberType($oNumberProto));
    }

    public static function isCmMTN(string $xTel) : bool
    {
        return self::getPhoneCarrier($xTel) === 'MTN';
    }

    public static function getPhoneCarrier(string $xTel, string $sCcode = 'CM') : ?string
    {
        if (null !== ($oNumberProto=self::getNumberProto($xTel, $sCcode))) {
            $oCarrierMapper = \libphonenumber\PhoneNumberToCarrierMapper::getInstance();
            $sCarrier = $oCarrierMapper->getNameForNumber($oNumberProto, "en");
            if (!empty($sCarrier)) {
                $asCarrier = explode(' ', $sCarrier);
                return strtoupper($asCarrier[0]);
            }
        }
        return null;
    }

    /**
     * Make clear sender
     *
     * If the originator ('from' field) is invalid, some networks may reject the network
     * whilst stinging you with the financial cost! While this cannot correct them, it
     * will try its best to correctly format them.
     */
    public static function clearSender(string $inp) : string
    {
        // Remove any invalid characters
        $ret = preg_replace('/[^a-zA-Z0-9]/', '', (string)$inp);

        if (preg_match('/[a-zA-Z]/', $inp)) {
            // Alphanumeric format so make sure it's < 11 chars
            $ret = mb_substr($ret, 0, 11);
        } else {
            // Numerical, remove any prepending '00'
            if (mb_substr($ret, 0, 2) == '00') {
                $ret = ltrim($ret, 0);
                $ret = mb_substr($ret, 0, 15);
            }
        }

        return (string)$ret;
    }
}
