<?php
namespace Headzoo\Web\Tools;
use Headzoo\Utilities\Complete;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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
     * Standard curl options
     * @var array
     */
    private static $curlOptions = [
        CURLOPT_USERAGENT       => self::DEFAULT_USER_AGENT,
        CURLOPT_RETURNTRANSFER  => true,
        CURLINFO_HEADER_OUT     => true,
        CURLOPT_HEADER          => true,
        CURLOPT_VERBOSE         => true,
        CURLOPT_FOLLOWLOCATION  => true
    ];

    /**
     * Used to log messages
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The logger message format
     * @var string
     */
    private $logFormat;
    
    /**
     * The url being requested
     * @var string
     */
    protected $url;

    /**
     * cURL resource to make the request
     * @var resource
     */
    protected $curl;

    /**
     * Used to release the curl resource
     * @var Complete
     */
    protected $complete;
    
    /**
     * Information about the last request
     * @var array
     */
    protected $info = [];

    /**
     * The request method
     * @var string
     */
    protected $method;
    
    /**
     * The get/post data
     * @var mixed
     */
    protected $data;

    /**
     * The basic auth username and password
     * @var array
     */
    protected $auth = [];
    
    /**
     * The user agent string
     * @var string
     */
    protected $userAgent;
    
    /**
     * Headers to send with the request
     * @var array
     */
    protected $headers = [
        "Content-Type" => self::DEFAULT_CONTENT_TYPE
    ];
    
    /**
     * Used to parse raw http request headers
     * @var Parsers\HeadersInterface
     */
    protected $headersParser;

    /**
     * Used to build raw http request headers
     * @var Builders\HeadersInterface
     */
    protected $headersBuilder;

    /**
     * The parsed response values
     * @var array
     */
    protected $response = [];

    /**
     * Constructor
     * 
     * @param string $method    The request method, one of the HttpMethods constants
     * @param string $userAgent The user agent string
     */
    public function __construct($method = HttpMethods::GET, $userAgent = self::DEFAULT_USER_AGENT)
    {
        $this->setMethod($method);
        $this->setUserAgent($userAgent);
    }

    /**
     * {@inheritDoc}
     */
    public function setMethod($method)
    {
        if (!in_array($method, HttpMethods::getValues())) {
            throw new Exceptions\InvalidArgumentException(
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
    public function request($url)
    {
        $this->url = $url;
        $this->response = [
            "time"      => time(),
            "method"    => $this->method,
            "version"   => null,
            "code"      => 0,
            "body"      => null,
            "info"      => [],
            "headers"   => []
        ];
        
        $this->validate();
        $this->prepareCurl();
        $this->prepareHeaders();
        $this->prepareData();
        $this->exec();
        
        return new WebResponse($this->response);
    }

    /**
     * {@inheritDoc}
     */
    public function get($url)
    {
        $this->setMethod(HttpMethods::GET);
        return $this->request($url);
    }

    /**
     * {@inheritDoc}
     */
    public function post($url, $data)
    {
        $this->setMethod(HttpMethods::POST);
        $this->setData($data);
        return $this->request($url);
    }

    /**
     * {@inheritDoc}
     */
    public function getInformation()
    {
        return $this->info;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadersParser(Parsers\HeadersInterface $headersParser)
    {
        $this->headersParser = $headersParser;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadersParser()
    {
        if (null === $this->headersParser) {
            $this->headersParser = new Parsers\Headers();
        }
        return $this->headersParser;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadersBuilder(Builders\HeadersInterface $headersBuilder)
    {
        $this->headersBuilder = $headersBuilder;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadersBuilder()
    {
        if (null === $this->headersBuilder) {
            $this->headersBuilder = new Builders\Headers();
        }
        return $this->headersBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger, $logFormat = self::DEFAULT_LOG_FORMAT)
    {
        $this->logger    = $logger;
        $this->logFormat = $logFormat;
        
        return $this;
    }

    /**
     * Validates the request values for correctness
     *
     * @throws Exceptions\WebException When an incorrect value is found
     */
    protected function validate()
    {
        if ($this->method === HttpMethods::POST && empty($this->data)) {
            throw new Exceptions\WebException(
                "Using method POST without data."
            );
        }
    }

    /**
     * Initializes the curl resource
     */
    protected function prepareCurl()
    {
        if ($this->complete) {
            $this->complete = null;
        }

        $this->curl = curl_init();
        $this->complete = Complete::factory(function() {
            if ($this->curl) {
                curl_close($this->curl);
            }
            $this->curl     = null;
            $this->complete = null;
        });

        foreach(self::$curlOptions as $option => $value) {
            curl_setopt($this->curl, $option, $value);
        }
        if ($this->method === HttpMethods::POST) {
            curl_setopt($this->curl, CURLOPT_POST, true);
        }
        if (!empty($this->auth)) {
            curl_setopt($this->curl, CURLOPT_USERPWD, sprintf(
                "%s:%s",
                $this->auth["user"],
                $this->auth["pass"]
            ));
        }
    }
    
    /**
     * Adds the data value to the curl request
     */
    protected function prepareData()
    {
        if (!empty($this->data)) {
            if ($this->method === HttpMethods::GET) {
                $this->url = Utils::appendUrlQuery($this->url, $this->data);
            } else if ($this->method === HttpMethods::POST) {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->data);
            }
        }
    }

    /**
     * Adds the set headers to the curl request
     */
    protected function prepareHeaders()
    {
        // Need to let curl set the content type when posting. Our own content type
        // value would overwrite that.
        if ($this->method === HttpMethods::POST && !empty($this->headers["Content-Type"])) {
            unset($this->headers["Content-Type"]);
        }
        
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgent);
        if (!empty($this->headers)) {
            $headers = $this->getHeadersBuilder()->normalize($this->headers);
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
    }

    /**
     * Executes the configured request
     */
    protected function exec()
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        $response_text = curl_exec($this->curl);
        $this->info    = curl_getinfo($this->curl);
        $this->logInformation();
        
        if (false === $response_text) {
            throw new Exceptions\WebException(
                curl_error($this->curl),
                curl_errno($this->curl)
            );
        }
        
        /** @var $options string */
        $this->response["info"] = $this->info;
        $this->response["code"] = $this->response["info"]["http_code"];
        $this->response["body"] = substr(
            $response_text,
            $this->response["info"]["header_size"]
        );
        $this->response["headers"] = substr(
            $response_text,
            0,
            $this->response["info"]["header_size"]
        );
        $this->response["headers"] = $this->getHeadersParser()->parse(
            $this->response["headers"],
            $options
        );
        $this->response["version"] = explode(" ", $options, 3)[0];
    }

    /**
     * Logs request information when logging is enabled
     */
    protected function logInformation()
    {
        if ($this->logger) {
            $level = $this->info["http_code"] > 0 && $this->info["http_code"] < 400
                ? LogLevel::INFO
                : LogLevel::ERROR;
            $this->info["method"] = $this->method;
            $this->logger->log($level, $this->logFormat, $this->info);
        }
    }
} 