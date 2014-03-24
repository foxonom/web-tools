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
     * The full request headers
     * @var array
     */
    protected $requestHeaders;

    /**
     * Information on the last request
     * @var array
     */
    protected $requestInfo = [];

    /**
     * The request method
     * @var string
     */
    protected $method = HttpMethods::GET;
    
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
        "Content-Type" => self::DEFAULT_CONTENT_TYPE
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
     * Used to release the curl resource
     * @var Complete
     */
    private $complete;

    /**
     * Used to parse the request headers
     * @var HttpHeadersParserInterface
     */
    private $headerParser;

    /**
     * Constructor
     * 
     * @param string $method    The request method, one of WebClient::METHOD_GET or WebClient::METHOD_POST
     * @param string $userAgent The user agent string
     */
    public function __construct($method = HttpMethods::GET, $userAgent = self::DEFAULT_USER_AGENT)
    {
        $this->setMethod($method);
        $this->setUserAgent($userAgent);
    }

    /**
     * Sets the object which will be used to parse request headers
     *
     * @param  HttpHeadersParserInterface $headersParser The headers parser
     * @return $this
     */
    public function setHeadersParser(HttpHeadersParserInterface $headersParser)
    {
        $this->headerParser = $headersParser;
        return $this;
    }

    /**
     * Returns the object which will be used to parse request headers
     *
     * @return HttpHeadersParserInterface
     */
    public function getHeadersParser()
    {
        if (null === $this->headerParser) {
            $this->headerParser = new HttpHeadersParser();
        }
        return $this->headerParser;
    }

    /**
     * {@inheritDoc}
     */
    public function setMethod($method)
    {
        if (!in_array($method, HttpMethods::getValues())) {
            throw new InvalidArgumentException(
                "Invalid request method."
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
            $this->headers[$header] = $value;
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
     * {@inheritDoc}
     */
    public function getRequestHeaders()
    {
        if (null === $this->requestHeaders) {
            $this->requestHeaders = [];
            if (!empty($this->requestInfo["request_header"])) {
                $headersParser        = $this->getHeadersParser();
                $this->requestHeaders = $headersParser->parse($this->requestInfo["request_header"]);
            }
        }
        
        return $this->requestHeaders;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestInfo()
    {
        return $this->requestInfo;
    }

    /**
     * {@inheritDoc}
     */
    public function request($url)
    {
        $this->url = $url;
        $this->prepareCurl();
        $this->validateRequest();

        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        $response = curl_exec($this->curl);
        $this->requestInfo = curl_getinfo($this->curl);
        $this->statusCode  = $this->requestInfo["http_code"];
        if (false === $response) {
            throw new Exceptions\WebException(
                curl_error($this->curl),
                curl_errno($this->curl)
            );
        }

        return $response;
    }
    
    /**
     * Prepares curl to make an http request using the class property values
     */
    protected function prepareCurl()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
            $this->curl = null;
        }
        if (null !== $this->complete) {
            $this->complete = null;
        }

        $this->curl     = curl_init();
        $this->complete = Complete::factory(function() {
            if ($this->curl) {
                curl_close($this->curl);
            }
            $this->complete = null;
        });
        
        $opts = [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_USERAGENT       => $this->userAgent,
            CURLINFO_HEADER_OUT     => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_POST            => $this->method === HttpMethods::POST
        ];
        if (!empty($this->auth)) {
            $opts[CURLOPT_USERPWD] = sprintf("%s:%s", $this->auth["user"], $this->auth["pass"]);
        }
        foreach($opts as $opt => $value) {
            curl_setopt($this->curl, $opt, $value);
        }

        $this->prepareHeaders();
        $this->prepareData();
    }

    /**
     * Adds the data value to the get/post request
     */
    protected function prepareData()
    {
        if (!empty($this->data)) {
            if ($this->method === HttpMethods::GET) {
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

    /**
     * Adds the headers to the request
     */
    protected function prepareHeaders()
    {
        // Need to let curl set the content type when posting. Our own content type
        // value would overwrite that.
        if ($this->method === HttpMethods::POST && !empty($this->headers["Content-Type"])) {
            unset($this->headers["Content-Type"]);
        }
        $headers = [];
        foreach($this->headers as $name => $value) {
            if (is_int($name)) {
                $headers[] = $value;
            } else {
                $headers[] = "{$name}: {$value}";
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
    }
    
    protected function validateRequest()
    {
        if ($this->method === HttpMethods::POST && empty($this->data)) {
            throw new Exceptions\WebException(
                "Using method POST without data."
            );
        }
    }
} 