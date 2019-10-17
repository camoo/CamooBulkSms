<?php
declare(strict_types=1);
namespace Camoo\Sms\Lib;

use \libphonenumber\PhoneNumberUtil;
use \libphonenumber\PhoneNumber;
use stdClass;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Console\BackgroundProcess;

class Utils
{
    public static function phoneUtil()
    {
        return PhoneNumberUtil::getInstance();
    }

    public static function getNumberProto(string $xTel, string $sCcode = null)
    {
        if (isset($xTel) && !empty($xTel)) {
            try {
                return self::phoneUtil()->parse($xTel, $sCcode);
            } catch (\libphonenumber\NumberParseException $e) {
                return null;
            }
        }
        return null;
    }

    public static function isValidPhoneNumber(string $xTel, string $sCcode, bool $bStrict = null) : bool
    {
        $bRet = ($oNumberProto=self::getNumberProto($xTel, $sCcode)) && self::phoneUtil()->isValidNumber($oNumberProto) && !empty(self::phoneUtil()->getNumberType($oNumberProto));
        if ($bRet && $bStrict === true) {
            return self::getPhoneRcode($oNumberProto) === strtoupper($sCcode);
        }
        return $bRet;
    }

    public static function getPhoneRcode(PhoneNumber $oNumberProto)
    {
        return self::phoneUtil()->getRegionCodeForNumber($oNumberProto);
    }

    public static function getPhoneCcode(PhoneNumber $oNumberProto)
    {
        return $oNumberProto->getCountryCode();
    }

    public static function isCmMTN(string $xTel) : bool
    {
        return self::getPhoneCarrier($xTel) === 'MTN';
    }

    public static function getPhoneCarrier(string $xTel, string $sCcode = 'CM')
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
                $ret = ltrim($ret, '0');
                $ret = mb_substr($ret, 0, 15);
            }
        }

        return (string)$ret;
    }

    public static function normaliseKeys($oResponse) : stdClass
    {
        $oNewRet = new stdClass();
        foreach ($oResponse as $sKey => $xVal) {
            if ($xVal instanceof stdClass || is_array($xVal)) {
                $xVal = self::normaliseKeys($xVal);
            }
            $oNewRet->{str_replace('-', '_', $sKey)} = $xVal;
        }
        return $oNewRet;
    }

    public static function getMessageKey(stdClass $oResponse, string $sKey)
    {
        if (property_exists($oResponse, 'sms') && property_exists($oResponse->sms, 'messages')) {
            foreach ($oResponse->sms->messages as $oMsg) {
                if (property_exists($oMsg, $sKey)) {
                    return $oMsg->{$sKey};
                }
            }
        }
        return null;
    }

    public static function doBulkSms(array $hData, array $hCredentials, array $hCallBack=[], $oMessage=null) : array
    {
        $asPersonlizeMsgFind = \Camoo\Sms\Constants::PERSONLIZE_MSG_KEYS;
        $iCount = 0;
        $axMsgSent = [];
        $batch_loop = 2;
        $iBatch = 1;
        $xTo = $hData['to'];
        $bIsMultiArray = self::isMultiArray($xTo);
        if (is_array($xTo) && !$bIsMultiArray) {
            $xTo = array_unique($xTo);
        }
        $defaultCallBack=['bulk_chunk' => \Camoo\Sms\Constants::SMS_MAX_RECIPIENTS];
        $hCallBack += $defaultCallBack;
        if ($hCallBack['bulk_chunk'] > 1 && !$bIsMultiArray) {
            $asDestinationNumbers = array_chunk($xTo, $hCallBack['bulk_chunk'], true);
        } else {
            $asDestinationNumbers = $xTo;
        }
        unset($hData['to']);
        unset($hCallBack['bulk_chunk']);
        $sMessageRaw = !empty($hData['message'])? $hData['message'] : null;
        foreach ($asDestinationNumbers as $xNumber) {
            $iCount++;
            try {
                call_user_func(\Camoo\Sms\Constants::CLEAR_OBJECT);
                $oMessage = null === $oMessage? \Camoo\Sms\Message::create($hCredentials['api_key'], $hCredentials['api_secret']) : $oMessage;

                // PERSONALZE
                $sName = '';
                if (is_array($xNumber) && !empty($xNumber['name'])) {
                    $sName = self::satanizer($xNumber['name']);
                }
                $asPersonlizeMsgReplace = [$sName];

                if (null !== $sMessageRaw) {
                    $hData['message'] = str_replace($asPersonlizeMsgFind, $asPersonlizeMsgReplace, $sMessageRaw);
                }

                if (is_array($xNumber) && !empty($xNumber['mobile'])) {
                    $xNumber = $xNumber['mobile'];
                }

                foreach ($hData as $key => $value) {
                    $oMessage->{$key} = $value;
                }

                $oMessage->to = $xNumber;
                $oResponse = $oMessage->send();
                $axMsgSent[]  = $oResponse;
                $hDataLock = $hData;
                $hDataLock['to'] = is_array($xNumber)? implode(",", $xNumber) : $xNumber;
                $hDataLock['message_id'] = static::getMessageKey($oResponse, 'message_id');
                $hDataLock['response'] = json_encode($oResponse);
                static::doBulkCallback($hCallBack, $hDataLock);
            } catch (CamooSmsException $err) {
                @trigger_error('ERREOR occured during sending SMS to' . $xNumber, E_USER_WARNING);
                continue;
            }
            if ($iCount === $batch_loop) {
                $batch_loop = $batch_loop + $iBatch;
                @sleep(4);
            }
        }
        return $axMsgSent;
    }

    public static function doBulkCallback($hCallBack, $data, $oDB=null)
    {
        if (!empty($hCallBack['driver']) && !empty($hCallBack['db_config'])) {
            try {
                $oDB = null === $oDB? $oDB = call_user_func_array($hCallBack['driver'], $hCallBack['db_config'])->getDB() : $oDB;
                if ($oDB) {
                    $hVariablesRow = array_key_exists('variables', $hCallBack)? $hCallBack['variables'] : [];
                    $hVariables = [];
                    foreach ($hVariablesRow as $key => $sMap) {
                        $hVariables[$key] = array_key_exists($sMap, $data)? $data[$sMap] : '';
                    }
                    $sPrefix = !empty($hCallBack['db_config'][0]['table_prefix'])? $hCallBack['db_config'][0]['table_prefix'] : '';
                    $sTableClient = !empty($hCallBack['db_config'][0]['table_sms'])? $hCallBack['db_config'][0]['table_sms'] : null;
                    if (!empty($hVariables) && !empty($sTableClient)) {
                        $sTable = $sPrefix.$sTableClient;
                        $oDB->insert($sTable, $hVariables);
                    }
                    $oDB->close();
                }
            } catch (\Exception | \TypeError $err) {
                trigger_error('ERROR: doBulkCallback SMS:: '.$err->getMessage(), E_USER_ERROR);
            }
        }
    }

    public static function randomStr()
    {
        $bytes = random_bytes(5);
        return bin2hex($bytes);
    }

    public static function backgroundProcess(array $hData, array $hCredentials, array $hCallBack=[])
    {
        $default = ['path_to_php' => 'php'];
        $hCallBack += $default;
        $sTmpName =  self::randomStr().'.bulk';
        if ($hCallBack['path_to_php'] === 'php' || is_executable($hCallBack['path_to_php'])) {
            if (file_put_contents(\Camoo\Sms\Constants::getSMSPath(). 'tmp/' .$sTmpName, json_encode($hData).PHP_EOL, LOCK_EX)) {
                $sBIN = $hCallBack['path_to_php'] .' -f '. \Camoo\Sms\Constants::getSMSPath(). 'bin/camoo.php';
                $sPASS = json_encode([$hCallBack,$sTmpName,$hCredentials]);
                $oProcess = new BackgroundProcess($sBIN .' ' .base64_encode($sPASS));
                return $oProcess->run();
            }
        }
        return 0;
    }

    public static function decodeJson($sJSON, $bAsHash = false)
    {
        if (($xData = json_decode($sJSON, $bAsHash)) !== null
                && (json_last_error() === JSON_ERROR_NONE)) {
            return $xData;
        }
    }

    public static function isMultiArray(array $option) : bool
    {
        rsort($option);
        return isset($option[0]) && is_array($option[0]);
    }

    public static function mapMobile($xValue)
    {
        if (is_string($xValue)) {
            return $xValue;
        }
        if (is_array($xValue) && !empty($xValue['mobile'])) {
            return self::phoneNumberE164Format($xValue['mobile']);
        }
        return null;
    }

    public static function makeNumberE164Format($xValue)
    {
        if (is_string($xValue)) {
            return [self::phoneNumberE164Format($xValue)];
        }
        if (is_array($xValue) && empty($xValue['mobile'])) {
            $xValue = array_map(function ($number) {
                if (is_string($number) || is_numeric($number)) {
                    return self::phoneNumberE164Format($number);
                }
            }, $xValue);
        }
        return array_filter($xValue);
    }

    public static function satanizer($str, $keep_newlines = false)
    {
        if (is_object($str) || is_array($str)) {
            return '';
        }
        $filtered = (string) $str;
        if (!mb_check_encoding($filtered, 'UTF-8')) {
            return '';
        }
        if (strpos($filtered, '<') !== false) {
            $callback = function ($match) {
                if (false === strpos($match[0], '>')) {
                    return htmlentities($match[0], ENT_QUOTES | ENT_IGNORE, "UTF-8");
                }
                return $match[0];
            };
            $filtered = preg_replace_callback('%<[^>]*?((?=<)|>|$)%', $callback, $filtered);
            $filtered = self::stripAllTags($filtered, false);
            $filtered = str_replace("<\n", "&lt;\n", $filtered);
        }
        if (! $keep_newlines) {
            $filtered = preg_replace('/[\r\n\t ]+/', ' ', $filtered);
        }
        $filtered = trim($filtered);
        $found = false;
        while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
            $filtered = str_replace($match[0], '', $filtered);
            $found    = true;
        }
        if ($found) {
            $filtered = trim(preg_replace('/ +/', ' ', $filtered));
        }
        return $filtered;
    }

    public static function stripAllTags($string, $remove_breaks = false)
    {
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);
        if ($remove_breaks) {
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        }
        return trim($string);
    }

    public static function phoneNumberE164Format(string $xTel)
    {
        if ($sTel = preg_replace('/[^\dxX]/', '', $xTel)) {
            return '+' .ltrim($sTel, '0');
        }
        return null;
    }
}
