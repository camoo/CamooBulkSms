<?php
namespace Camoo\Sms;

use Camoo\Sms\Exception\CamooSmsException;

/**
 * Class Base
 *
 */
class Base
{

    protected $sEndPoint = 'https://api.camoo.cm';
    
    protected static $_dataObject = null;

     /**
     * @var string The resource name as it is known at the server
     */
    protected $resourceName = null;
    protected static $_credentials = [];
    protected static $_ahConfigs = [];
    protected static $_create = null;

    /**
     * @param $resourceName
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @return Objects
     * @throws Exception\CamooSmsException
     */
    public static function create()
    {
        $sConfigFile = dirname(__DIR__) . Constants::DS.'config'.Constants::DS.'app.php';
        if (!file_exists($sConfigFile)) {
            throw new CamooSmsException(['config' => 'config/app.php is missing!']);
        }
        static::$_ahConfigs = (require $sConfigFile);
        static::$_credentials = static::$_ahConfigs['App'];
        $sClass = get_called_class();
        $asCaller = explode('\\', $sClass);
        $sCaller  = array_pop($asCaller);
        $sObjecClass = '\\Camoo\\Sms\\Objects\\'.$sCaller;
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
    public function getConfigs()
    {
        return self::$_ahConfigs;
    }

    /**
     * @return array
     */
    public function getCredentials()
    {
        return self::$_credentials;
        ;
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

    public function getData($sValidator = 'default')
    {
        try {
            return Objects\Base::create()->get($this->getDataObject(), $sValidator);
        } catch (CamooSmsException $err) {
            echo $err->getMessage();
            die;
        }
    }

     /**
      * Returns the CAMOO API URL
      *-
      * @return string
      * @author Epiphane Tchabom
      **/
    public function getEndPointUrl()
    {
        $sUrlTmp = $this->sEndPoint.Constants::DS.$this->camooClassicApiVersion.Constants::DS;
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
            return $this->$sDecoder($sBody);
        } catch (CamooSmsException $e) {
            return $e->getMessage();
        }
    }

    private function decodeJson($sBody, $bAsHash = false)
    {
        try {
            if (($xData = json_decode($sBody, $bAsHash)) === null
                    && (json_last_error() !== JSON_ERROR_NONE) ) {
                throw new CamooSmsException(json_last_error_msg());
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $xData;
    }

    private function decodeXml($sBody)
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
     * @param $sDataName
     *
     * @return mixed
     * @author Epiphane Tchabom
     */
    protected function execRequest($sRequestType, $bWithData = true, $sDataName = null)
    {
        $oHttpClient = new HttpClient($this->getEndPointUrl(), $this->getCredentials());
        $data=[];
        if ($bWithData === true) {
            $data = is_null($sDataName)? $this->getData() : $this->getData($sDataName);
        }
        return $this->decode($oHttpClient->performRequest($sRequestType, $data));
    }
}
