<?php
declare(strict_types=1);
namespace Camoo\Sms\Console;

use Camoo\Sms\Exception\CamooSmsException;

class Runner
{
    public function run($argv)
    {
        if (isset($argv)) {
            $sPASS = $argv[1];
            if ($asPASS = \Camoo\Sms\Lib\Utils::decodeJson(base64_decode($sPASS), true)) {
                $hCallBack = $asPASS[0];
                $sTmpName  = $asPASS[1];
                $hCredentials = $asPASS[2];
                $sFile = \Camoo\Sms\Constants::getSMSPath(). 'tmp/' .$sTmpName;
                if (file_exists($sFile) && is_readable($sFile)) {
                    if (($sData = file_get_contents($sFile)) && ($hData = \Camoo\Sms\Lib\Utils::decodeJson($sData, true))) {
                        unlink($sFile);
                        \Camoo\Sms\Lib\Utils::doBulkSms($hData, $hCredentials, $hCallBack);
                    }
                }
            }
        }
    }
}
