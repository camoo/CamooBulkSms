#!/usr/bin/php
<?php

require_once dirname(__DIR__) . '/autoload.php';
class Runner
{
    public function run()
    {
        if ($_SERVER['argc'] > 1) {
            $sPASS = $_SERVER['argv'][1];
            $asPASS = \Camoo\Sms\Lib\Utils::decodeJson(base64_decode($sPASS), true);
            $hCallBack = $asPASS[0];
            $sTmpName = $asPASS[1];
            $hCredentials = $asPASS[2];

            $oDBInstance = call_user_func_array($hCallBack['driver'], $hCallBack['db_config']);
            $oDB = $oDBInstance->getDB();
            $sFile = \Camoo\Sms\Constants::getSMSPath(). 'tmp/' .$sTmpName;
            if (file_exists($sFile)) {
                $sData = file_get_contents($sFile);
                $hData = \Camoo\Sms\Lib\Utils::decodeJson($sData, true);
                unlink($sFile);
            }
			//TODO
        }
    }
}
$oRunner = new Runner();
$oRunner->run();
