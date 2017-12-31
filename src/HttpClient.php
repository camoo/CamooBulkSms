<?php
namespace Camoo\Sms;

use Camoo\Sms\Exception\HttpClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Valitron\Validator;

/**
 * Class HttpClient
 *
 */
class HttpClient
{
    const HTTP_NO_CONTENT = 204;
    const CLIENT_VERSION = '3.0.0';
    const MIN_PHP_VERSION = 50600;
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var array
     */
    protected $userAgent = array();

    /**
     * @var array
     */
    protected $hRequestVerbs = ['GET' => 'query', 'POST' => 'form_params'];

    /**
     * @var int
     */
    private $timeout = 10;
    
    /**
    * @var mixed
    */
    private $hAuthentication = [];
    
    /**
     * @var object
     */
    private $oClient = null;

    /**
     * @param string $endpoint
     * @param int $timeout > 0
     *
     * @throws \HttpClientException if timeout settings are invalid
     */
    public function __construct($endpoint, $hAuthentication, $timeout = 10)
    {
        $this->endpoint = $endpoint;
        $this->hAuthentication = $hAuthentication;
    
        $this->addUserAgentString('CamooSms/ApiClient/' . static::CLIENT_VERSION);
        $this->addUserAgentString($this->getPhpVersion());

        if (!is_int($timeout) || $timeout < 0) {
            throw new HttpClientException(sprintf(
                'Connection timeout must be an int >= 0, got "%s".',
                is_object($timeout) ? get_class($timeout) : gettype($timeout).' '.var_export($timeout, true)
            ));
        }

        $this->timeout = $timeout;


        if (is_null($this->oClient)) {
            $this->oClient = new Client(['timeout' => $this->timeout]);
        }
    }

    /**
     * Validate request params
     *
     * @param Validator $oValidator
     *
     * @return boolean
     */
    private function ValidatorDefault(Validator $oValidator)
    {
        $oValidator->rule('required', ['api_key', 'api_secret', 'response_format']);
        $oValidator->rule('in', 'response_format', ['json', 'xml']);
        return $oValidator->rule('in', 'request', array_keys($this->hRequestVerbs))->validate();
    }

    /**
     * @param string $userAgent
     */
    public function addUserAgentString($userAgent)
    {
        $this->userAgent[] = $userAgent;
    }


    /**
     * @param string      $method
     * @param string|null $data
     *
     * @return array
     *
     * @throws HttpClientException
     */
    public function performRequest($method, $data = array())
    {
        if (PHP_VERSION_ID < static::MIN_PHP_VERSION) {
               trigger_error("Your PHP-Version belongs to a release that is no longer supported. You should upgrade your PHP version as soon as possible, as it may be exposed to unpatched security vulnerabilities.", E_USER_ERROR);
        }
        // Build the post data
        $data = array_merge($data, $this->hAuthentication);
        $data['user_agent'] = implode(' ', $this->userAgent);

    // VALIDATE REQUEST
        $sMethod = strtoupper($method);
        $oValidator = new Validator(array_merge(['request' => $sMethod], $data));
        if (empty($this->ValidatorDefault($oValidator))) {
            throw new HttpClientException('Request not allowed!');
        }

    //  UNSET REQUEST FORMAT
        unset($data['response_format']);

        try {
            $oResponse = $this->oClient->request($sMethod, $this->endpoint, [$this->hRequestVerbs[$sMethod] => $data]);
            if ($oResponse->getStatusCode() === 200) {
                return $oResponse->getBody();
            }
            throw new HttpClientException();
        } catch (RequestException $e) {
            throw new HttpClientException(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                throw new HttpClientException(Psr7\str($e->getResponse()));
            }
        }
    }
    
     /**
     * @return string
     */
    private function getPhpVersion()
    {
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]);
        }
        return 'PHP/' . PHP_VERSION_ID;
    }
}
