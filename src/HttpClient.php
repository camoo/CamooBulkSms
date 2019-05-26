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
    const GET_REQUEST = 'GET';
    const POST_REQUEST = 'POST';
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
    protected $hRequestVerbs = [self::GET_REQUEST => 'query', self::POST_REQUEST => 'form_params'];

    /**
     * @var int
     */
    private $timeout = Constants::CLIENT_TIMEOUT;
    
    /**
    * @var mixed
    */
    private $hAuthentication = [];
    
    /**
     * @var object
     */
    private $oClient = null;

    /**
     * @var array
     */
    private $_headers = [];

    /**
     * @param string $endpoint
     * @param int $timeout > 0
     *
     * @throws \HttpClientException if timeout settings are invalid
     */
    public function __construct($endpoint, $hAuthentication, $timeout = 0)
    {
        $this->endpoint = $endpoint;
        $this->hAuthentication = $hAuthentication;
    
        $this->addUserAgentString('CamooSms/ApiClient/' . Constants::CLIENT_VERSION);
        $this->addUserAgentString(Constants::getPhpVersion());

        if (!is_int($timeout) || $timeout < 0) {
            throw new HttpClientException(sprintf(
                'Connection timeout must be an int >= 0, got "%s".',
                is_object($timeout) ? get_class($timeout) : gettype($timeout).' '.var_export($timeout, true)
            ));
        }
        if (!empty($timeout)) {
            $this->timeout = $timeout;
        }

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
    private function validatorDefault(Validator $oValidator)
    {
        $oValidator->rule('required', ['X-Api-Key', 'X-Api-Secret', 'response_format']);
        $oValidator->rule('optional', ['User-Agent']);
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
    public function performRequest($method, $data = array(), $headers = array())
    {
        $this->setHeader($headers);
        //VALIDATE HEADERS
        $hHeaders = $this->getHeaders();
        $sMethod = strtoupper($method);
        $oValidator = new Validator(array_merge(['request' => $sMethod, 'response_format' => $this->getEndPointFormat()], $hHeaders));
        if (empty($this->validatorDefault($oValidator))) {
            throw new HttpClientException(json_encode($oValidator->errors()));
        }


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

    protected function getAuthKeys()
    {
        return $this->hAuthentication;
    }

    protected function setHeader($option = [])
    {
        $this->_headers += $option;
    }

    protected function getHeaders()
    {
        $default = [];
        if ($hAuth = $this->getAuthKeys()) {
            $default = [
                'X-Api-Key'    => $hAuth['api_key'],
                'X-Api-Secret' => $hAuth['api_secret'],
                'User-Agent'   => $this->getUserAgentString()
            ];
        }
        return $this->_headers += $default;
    }

    protected function getEndPointFormat()
    {
        $asEndPoint = explode('.', $this->endpoint);
        return end($asEndPoint);
    }
}
