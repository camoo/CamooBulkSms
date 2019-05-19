<?php
declare(strict_types=1);
namespace Camoo\Sms;

use Camoo\Sms\Exception\CamooSmsException;

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
     * @param $resourceName
     */
    public function setResourceName(string $resourceName)
    {
        $this->_resourceName = $resourceName;
    }

    /**
     * @return string
     */
    public function getResourceName() : ?string
    {
        return $this->_resourceName;
    }

    /**
     * @return Objects
     * @throws Exception\CamooSmsException
     */
    public static function create($api_key = null, $api_secret = null)
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
            static::$_ahConfigs = ['local_login' => false, 'App' => ['response_format' => 'json']];
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

     /**
     *  @var string Target version for "Classic" Camoo API
     */
    protected $camooClassicApiVersion = 'v1';


    public function __get($property)
    {
        $hPayload = Objects\Base::create()->get($this->getDataObject());
        return $hPayload[$property];
    }

    public function __set($property, $value)
    {
        try {
            Objects\Base::create()->set($property, $value, $this->getDataObject());
        } catch (CamooSmsException $err) {
            echo $err->getMessage();
            exit();
        }
        return $this;
    }

    /**
     * Returns payload for a request
     *
     * @param $sValidator
     * @return Array
     */
    public function getData(?string $sValidator = 'default') : array
    {
        try {
            return Objects\Base::create()->get($this->getDataObject(), $sValidator);
        } catch (CamooSmsException $err) {
            trigger_error($err->getMessage(), E_USER_ERROR);
            return [];
        }
    }

     /**
      * Returns the CAMOO API URL
      *
      * @return string
      * @author Epiphane Tchabom
      **/
    public function getEndPointUrl() : string
    {
        $sUrlTmp = $this->_endPoint.Constants::DS.$this->camooClassicApiVersion.Constants::DS;
        $sResource = '';
        if ($this->getResourceName() !== null && $this->getResourceName() !== 'sms') {
            $sResource = Constants::DS.$this->getResourceName();
        }
        $response_format = !empty(static::$_ahConfigs['App']['response_format'])? static::$_ahConfigs['App']['response_format'] : 'json';
        return sprintf($sUrlTmp.'sms'.$sResource.'%s', '.' . $response_format);
    }
    
    /**
     * decode camoo response string
     *
     * @param $sBody
     * @throw CamooSmsException
     * @author Epiphane Tchabom
     */
    protected function decode($sBody)
    {
        try {
            $sDecoder = 'decode' .ucfirst(static::$_ahConfigs['App']['response_format']);
            return $this->{$sDecoder}($sBody);
        } catch (CamooSmsException $e) {
            return $e->getMessage();
        }
    }

    /**
     * decodes response json string
     *
     * @param $sBody
     * @param $bAsHash
     *
     * @throw CamooSmsException
     * @return object | Array
     */
    private function decodeJson(string $sBody)
    {
        try {
            if (($xData = json_decode($sBody)) === null
                    && (json_last_error() !== JSON_ERROR_NONE) ) {
                throw new CamooSmsException(json_last_error_msg());
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $xData;
    }

    /**
     * decodes response xml string
     *
     * @param $sBody
     *
     * @throw CamooSmsException
     * @return string (xml)
     */
    private function decodeXml(string $sBody) : string
    {
        try {
            $oXML = new \SimpleXMLElement($sBody);
            if (($xData =$oXML->asXML()) === false) {
                throw new CamooSmsException('response couldn\'t be decoded');
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $xData;
    }

    /**
     * Execute request with credentials
     *
     * @param $sRequestType
     * @param $bWithData
     * @param $sObjectValidator
     *
     * @return mixed
     * @author Epiphane Tchabom
     */
    protected function execRequest(string $sRequestType, $bWithData = true, $sObjectValidator = null)
    {
        $oHttpClient = new HttpClient($this->getEndPointUrl(), $this->getCredentials());
        $data = [];
        if ($bWithData === true) {
            $data = null === $sObjectValidator? $this->getData() : $this->getData($sObjectValidator);
            $oClassObj = $this->getDataObject();
            if (is_object($oClassObj) && $oClassObj instanceof \Camoo\Sms\Objects\Message && array_key_exists('message', $data) && $oClassObj->encrypt === true) {
                $data['message'] = $this->encryptMsg($data['message']);
            }
        }
        if (array_key_exists('encrypt', $data)) {
            unset($data['encrypt']);
        }
        return $this->decode($oHttpClient->performRequest($sRequestType, $data));
    }

    /**
     * Encrypt message using PGP
     *
     * @param string $sMessage
     * @return string encrpted $sMessage
     */
    protected function encryptMsg(string $sMessage) : string
    {
        $sPubFile = dirname(__DIR__) . Constants::DS.'config'.Constants::DS.'keys' . Constants::DS . 'cert.pem';
        if (!file_exists($sPubFile) || ($sContent = file_get_contents($sPubFile)) === false ) {
            return $sMessage;
        }
        try {
            $oPGP = new \nicoSWD\GPG\GPG();
            $sPubKey = new \nicoSWD\GPG\PublicKey($sContent);
            return $oPGP->encrypt($sPubKey, $sMessage);
        } catch( CamooSmsException $err) {
            trigger_error($err->getMessage(), E_USER_ERROR);
            return $sMessage;
        }
    }
}
