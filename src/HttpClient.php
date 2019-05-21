<?php
declare(strict_types=1);
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
    protected $endpoint = null;

    /**
     * @var array
     */
    protected $userAgent = [];

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

        if ($this->oClient === null) {
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
    private function validatorDefault(Validator $oValidator) : bool
    {
        $oValidator->rule('required', ['X-Api-Key', 'X-Api-Secret']);
        $oValidator->rule('optional', ['User-Agent', 'response_format']);
        $oValidator->rule('in', 'response_format', ['json', 'xml']);
        return $oValidator->rule('in', 'request', array_keys($this->hRequestVerbs))->validate();
    }

    /**
     * @param string $userAgent
     */
    public function addUserAgentString(string $userAgent)
    {
        $this->userAgent[] = $userAgent;
    }

    /**
     * @return strin userAgentString
     */
    protected function getUserAgentString() : string
    {
        return implode(' ', $this->userAgent);
    }

    /**
     * @param string      $method
     * @param string|null $data
     *
     * @return array
     *
     * @throws HttpClientException
     */
    public function performRequest(string $method, array $data = [], array $headers = [])
    {
        $this->setHeader($headers);
        //VALIDATE HEADERS
        $hHeaders = $this->getHeaders();
        $sMethod = strtoupper($method);
        $oValidator = new Validator(array_merge(['request' => $sMethod,], $hHeaders));
        if (empty($this->validatorDefault($oValidator))) {
            throw new HttpClientException('Request not allowed!');
        }

        //UNSET REQUEST FORMAT
        if (array_key_exists('response_format', $data)) {
            unset($data['response_format']);
        }

        try {
            $oResponse = $this->oClient->request($sMethod, $this->endpoint, [$this->hRequestVerbs[$sMethod] => $data, 'headers' => $hHeaders]);
            if ($oResponse->getStatusCode() === 200) {
                return (string) $oResponse->getBody();
            }
            throw new HttpClientException();
        } catch (RequestException $e) {
            throw new HttpClientException(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                throw new HttpClientException(Psr7\str($e->getResponse()));
            }
        }
    }

    protected function getAuthKeys() : array
    {
        return $this->hAuthentication;
    }

    protected function setHeader(array $option = [])
    {
        $this->_headers += $option;
    }

    protected function getHeaders() : array
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
}
