<?php
declare(strict_types=1);

namespace Camoo\Sms;

use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Exception\HttpClientException;
use SimpleXMLElement;
use Exception;

/**
 * Class Base
 *
 */
class Base
{
    /*@ var string API endpoint */
    protected $_endPoint = Constants::END_POINT_URL;
    
    /*@ var object ressource */
    protected static $_dataObject = null;

    /**
    * @var string The resource name as it is known at the server
    */
    protected $_resourceName = null;

    /* @var mixed credentials */
    protected static $_credentials = [];

    /* @var mixed configs*/
    protected static $_ahConfigs = [];

    /* @var object instance*/
    protected static $_create = null;

    /**
    *  @var string Target version for "Classic" Camoo API
    */
    protected $camooClassicApiVersion = Constants::END_POINT_VERSION;

    /* @var Array Errors */
    private $_errors = [];

    /**
     * @param $resourceName
     */
    public function setResourceName(string $resourceName) : void
    {
        $this->_resourceName = $resourceName;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resourceName;
    }

    /**
     * @return Objects
     * @throws Exception\CamooSmsException
     */
    public static function create(string $api_key = null, string $api_secret = null)
    {
        $sConfigFile = dirname(__DIR__) . Constants::DS.'config'.Constants::DS.'app.php';
        if ((null === $api_key || null === $api_secret) && !file_exists($sConfigFile)) {
            throw new CamooSmsException(['config' => 'config/app.php is missing!']);
        }
        if (file_exists($sConfigFile)) {
            static::$_ahConfigs = (require $sConfigFile);
            static::$_credentials = static::$_ahConfigs['App'];
        }

        if ((null !== $api_key && null !== $api_secret) || empty(static::$_ahConfigs['local_login'])) {
            static::$_ahConfigs = ['local_login' => false, 'App' => ['response_format' => Constants::RESPONSE_FORMAT]];
            static::$_credentials = array_merge(static::$_ahConfigs['App'], array_combine(Constants::$asCredentialKeyWords, [$api_key, $api_secret]));
        }
        $sClass = get_called_class();
        $asCaller = explode('\\', $sClass);
        $sCaller  = array_pop($asCaller);
        $sObjecClass = Constants::APP_NAMESPACE.'Objects\\'.$sCaller;
        if (class_exists($sObjecClass)) {
            static::$_dataObject = new $sObjecClass();
        }

        if (is_null(static::$_create)) {
            if ($sClass !== __CLASS__) {
                static::$_create = new $sClass();
            } else {
                static::$_create = new self;
            }
        }
        return static::$_create;
    }

    public static function clear() : void
    {
        static::$_create = null;
    }

    /**
     * @return object
     */
    public function getDataObject()
    {
        return self::$_dataObject;
    }
        
    /**
     * @return array
     */
    public function getConfigs() : array
    {
        return self::$_ahConfigs;
    }

    /**
     * @return array
     */
    public function getCredentials() : array
    {
        return self::$_credentials;
    }

    public function __get(string $property)
    {
        $hPayload = Objects\Base::create()->get($this->getDataObject());
        return $hPayload[$property];
    }

    public function __set(string $property, $value)
    {
        try {
            Objects\Base::create()->set($property, $value, $this->getDataObject());
        } catch (CamooSmsException $err) {
            $this->_errors[] = $err->getMessage();
        }
        return $this;
    }

    /**
     * Returns payload for a request
     *
     * @param $sValidator
     * @return Array
     */
    public function getData($sValidator = 'default') : array
    {
        try {
            return Objects\Base::create()->get($this->getDataObject(), $sValidator);
        } catch (CamooSmsException $err) {
            $this->_errors[] = $err->getMessage();
        }
        return [];
    }

    protected function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Returns the CAMOO API URL
     *
     * @return string
     * @author Camoo Sarl
     **/
    public function getEndPointUrl() : string
    {
        $sUrlTmp = $this->_endPoint.Constants::DS.$this->camooClassicApiVersion.Constants::DS;
        $sResource = '';
        if ($this->getResourceName() !== null && $this->getResourceName() !== 'sms') {
            $sResource = Constants::DS.$this->getResourceName();
        }
        return sprintf($sUrlTmp.'sms'.$sResource.'%s', '.' . $this->getResponseFormat());
    }
    
    /**
     * decode camoo response string
     *
     * @param $sBody
     * @throw CamooSmsException
     * @author Camoo Sarl
     */
    protected function decode(string $sBody)
    {
        $sDecoder = 'decode' .ucfirst($this->getResponseFormat());
        return $this->{$sDecoder}($sBody);
    }

    /**
     * decodes response json string
     *
     * @param string $sBody
     *
     * @return stdClass
     */
    private function decodeJson(string $sBody)
    {
        if (($oData = json_decode($sBody)) !== null
                    && (json_last_error() === JSON_ERROR_NONE)) {
            return Lib\Utils::normaliseKeys($oData);
        }
    }

    /**
     * decodes response xml string
     *
     * @param $sBody
     *
     * @throw CamooSmsException
     * @return string (xml)
     */
    private function decodeXml(string $sBody)
    {
        try {
            $oXML = new SimpleXMLElement($sBody);
            if (($sData =$oXML->asXML()) !== false) {
                return $sData;
            }
        } catch (Exception $err) {
            print $err->getMessage();
        }
    }

    /**
     * Execute request with credentials
     *
     * @param $sRequestType
     * @param $bWithData
     * @param $sObjectValidator
     *
     * @return mixed
     * @author Camoo Sarl
     */
    public function execRequest(string $sRequestType, bool $bWithData = true, string $sObjectValidator = null, $oClient=null)
    {
        $oHttpClient = $oClient === null? new HttpClient($this->getEndPointUrl(), $this->getCredentials()) : $oClient;
        $data = [];
        if ($bWithData === true && empty($this->getErrors())) {
            $data = null === $sObjectValidator? $this->getData() : $this->getData($sObjectValidator);
            $oClassObj = $this->getDataObject();
            if (is_object($oClassObj) && $oClassObj instanceof \Camoo\Sms\Objects\Message && array_key_exists('message', $data) && $oClassObj->encrypt === true) {
                $sPerm = !array_key_exists('pgp_public_file', $data)? null : $data['pgp_public_file'];
                $data['message'] = $this->encryptMsg($data['message'], $sPerm);
            }
            if (is_object($oClassObj) && $oClassObj instanceof \Camoo\Sms\Objects\Message && array_key_exists('to', $data)) {
                $xTo = $data['to'];
                $data['to'] = is_array($xTo)? implode(',', array_map(Constants::MAP_MOBILE, $xTo)) : $xTo;
            }
        }
        if (!empty($this->getErrors())) {
            throw new CamooSmsException(['_error' => $this->getErrors()]);
        }
        if (array_key_exists('encrypt', $data)) {
            unset($data['encrypt']);
        }
        try {
            return $this->decode($oHttpClient->performRequest($sRequestType, $data));
        } catch (HttpClientException $err) {
            throw new CamooSmsException(['_error' => $err->getMessage()]);
        }
    }

    /**
     * Encrypt message using PGP
     *
     * @param string $sMessage
     * @return string encrpted $sMessage
     */
    protected function encryptMsg(string $sMessage, $sPubFile=null) : string
    {
        $sPubFile = null !== $sPubFile? $sPubFile : dirname(__DIR__) . Constants::DS.'config'.Constants::DS.'keys' . Constants::DS . 'cert.pem';
        if (!file_exists($sPubFile) || empty($sContent = file_get_contents($sPubFile))) {
            return $sMessage;
        }
        try {
            $oPGP = new \nicoSWD\GPG\GPG();
            $sPubKey = new \nicoSWD\GPG\PublicKey($sContent);
            return $oPGP->encrypt($sPubKey, $sMessage);
        } catch (Exception $err) {
            $this->_errors[] = $err->getMessage();
            return $sMessage;
        }
    }

    public function setResponseFormat(string $format) : void
    {
        static::$_ahConfigs['App']['response_format'] = strtolower($format);
    }

    public function getResponseFormat() : string
    {
        return !empty(static::$_ahConfigs['App']['response_format'])? static::$_ahConfigs['App']['response_format'] : Constants::RESPONSE_FORMAT;
    }

    public function execBulk($hCallBack)
    {
        $oClassObj = $this->getDataObject();
        if (!$oClassObj->has('to') || empty($oClassObj->to)) {
            return false;
        }
        return Lib\Utils::backgroundProcess($this->getData(), $this->getCredentials(), $hCallBack);
    }
}
