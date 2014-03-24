<?php
namespace Headzoo\Web\Tools;
use Headzoo\Utilities\Complete;
use InvalidArgumentException;

/**
 * Used to make http requests.
 * 
 * Example:
 * ```php
 * $http     = new WebClient();
 * $response = $http->request("http://example.com");
 * $status   = $http->getStatusCode();
 * ```
 */
class WebClient
    implements WebClientInterface
{
    /**
     * The http status code returned by the requested server
     * @var int
     */
    protected $statusCode = 200;

    /**
     * The request method
     * @var string
     */
    protected $method = self::METHOD_GET;
    
    /**
     * The get/post data
     * @var mixed
     */
    protected $data;

    /**
     * Headers to send with the request
     * @var array
     */
    protected $headers = [
        "Content-Type: text/html"
    ];

    /**
     * The user agent string
     * @var string
     */
    protected $userAgent;

    /**
     * The basic auth username and password
     * @var array
     */
    protected $auth = [];

    /**
     * The url being requested
     * @var string
     */
    private $url;
    
    /**
     * cURL resource to make the request
     * @var resource
     */
    private $curl;

    /**
     * Constructor
     * 
     * @param string $method    The request method, one of WebClient::METHOD_GET or WebClient::METHOD_POST
     * @param string $userAgent The user agent string
     */
    public function __construct($method = self::METHOD_GET, $userAgent = self::DEFAULT_USER_AGENT)
    {
        $this->setMethod($method);
        $this->setUserAgent($userAgent);
    }
    
    /**
     * {@inheritDoc}
     */
    public function request($url)
    {
        $this->url = $url;
        $this->prepareCurl();
        $this->prepareData();
        
        /** @noinspection PhpUnusedLocalVariableInspection */
        $complete = Complete::factory(function() {
            $this->url = null;
            curl_close($this->curl);
        });
        
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);
        $this->statusCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if (false === $response) {
            $error = curl_error($this->curl);
            $code  = curl_errno($this->curl);
            throw new Exceptions\WebException($error, $code);
        }
        
        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function setMethod($method)
    {
        if ($method !== self::METHOD_GET && $method !== self::METHOD_POST) {
            throw new InvalidArgumentException(
                "Method must be either 'GET' or 'POST'."
            );
        }
        $this->method = $method;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addHeader($header, $value = null)
    {
        if (null !== $value) {
            $this->headers[] = "{$header}: {$value}"; 
        } else {
            $this->headers[] = $header;
        }
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setBasicAuth($user, $pass)
    {
        $this->auth["user"] = (string)$user;
        $this->auth["pass"] = (string)$pass;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Prepares curl to make an http request using the class property values
     */
    protected function prepareCurl()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }

        $this->curl = curl_init();
        $opts = [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => $this->headers,
            CURLOPT_USERAGENT       => $this->userAgent,
            CURLOPT_POST            => $this->method === self::METHOD_POST
        ];
        if (!empty($this->auth)) {
            $opts[CURLOPT_USERPWD] = sprintf("%s:%s", $this->auth["user"], $this->auth["pass"]);
        }
        foreach($opts as $opt => $value) {
            curl_setopt($this->curl, $opt, $value);
        }
    }

    /**
     * Adds the data value to the get/post request
     */
    protected function prepareData()
    {
        if (!empty($this->data)) {
            if ($this->method === self::METHOD_GET) {
                if (is_array($this->data)) {
                    $data = http_build_query($this->data);
                } else {
                    $data = $this->data;
                }
                $combiner  = (strpos($this->url, "?") === false) ? "?" : "&";
                $this->url = "{$this->url}{$combiner}{$data}";
            } else {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->data);
            }
        }
    }
} 