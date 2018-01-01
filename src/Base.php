<?php
namespace Camoo\Sms;

use Camoo\Sms\Exception\CamooSmsException;

/**
 * Class Base
 *
 */
class Base
{

    const DS = '/';
    protected $sEndPoint = 'https://api.camoo.cm';
    
    protected static $dataObject = null;

     /**
     * @var string The resource name as it is known at the server
     */
    protected $resourceName = null;


    protected static $hCredentials = [];
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

    public static function create($dataObject = null)
    {
        if (is_null(static::$_create)) {
            static::$_create = new self;
        }
        $sConfigFile = dirname(__DIR__) . static::DS.'config'.static::DS.'app.php';
        if (!file_exists($sConfigFile)) {
            throw new CamooSmsException(['config' => 'config/app.php is missing!']);
        }
        static::$_ahConfigs = (require $sConfigFile);
        static::$hCredentials = static::$_ahConfigs['App'];
        return static::$_create;
    }

    /**
     * @return object
     */
    public function getDataObject()
    {
        return self::$dataObject;
    }
        
    /**
     * @return array
     */
    public function getConfigs()
    {
        return self::$_ahConfigs;
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

    public function getData()
    {
        try {
            return Objects\Base::create()->get($this->getDataObject());
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
        $sUrlTmp = $this->sEndPoint.static::DS.$this->camooClassicApiVersion.static::DS;
        $sResource = '';
        if ($this->getResourceName() !== null && $this->getResourceName() !== 'sms') {
            $sResource = static::DS.$this->getResourceName();
        }
        $response_format = !empty(static::$_ahConfigs['App']['response_format'])? static::$_ahConfigs['App']['response_format'] : 'json';
        return sprintf($sUrlTmp.'sms'.$sResource.'%s', '.' . $response_format);
    }
    
     /**
      * decode camoo response string
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
}
