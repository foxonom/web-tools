<?php
namespace Headzoo\Web\Tools;
use Psr\Log\LoggerInterface;

/**
 * Interface for classes which make http requests.
 */
interface WebClientInterface
{
    /**
     * The default user agent
     */
    const DEFAULT_USER_AGENT = "headzoo/web-tools";

    /**
     * The default content type
     */
    const DEFAULT_CONTENT_TYPE = "text/plain";

    /**
     * The default log message format
     */
    const DEFAULT_LOG_FORMAT = "[{http_code}] {method} {url}";

    /**
     * Sets the object which will be used to parse raw request headers
     *
     * @param  Parsers\HeadersInterface $headersParser The raw headers parser
     * @return $this
     */
    public function setHeadersParser(Parsers\HeadersInterface $headersParser);

    /**
     * Returns the object which will be used to parse raw request headers
     *
     * @return Parsers\HeadersInterface
     */
    public function getHeadersParser();

    /**
     * Sets the object which will be used to create raw http headers
     *
     * @param  Builders\HeadersInterface $headersBuilder The raw headers builder
     * @return $this
     */
    public function setHeadersBuilder(Builders\HeadersInterface $headersBuilder);

    /**
     * Returns the object which will be used to create raw request headers
     *
     * @return Builders\HeadersInterface
     */
    public function getHeadersBuilder();

    /**
     * Sets a logger instance and log format
     *
     * Once set, requests and errors will be logged using this logger. The log format is the string
     * actually written to the log. Place holders in the format of "{name}" will be replaced
     * with values from the WebClientInterface::getInformation() array. For example the
     * format "{http_code} {method} {url}" results in a log being written like "200 GET http://site.com".
     * 
     * See the WebClientInterface::getInformation() for information on which values may be used
     * as place holders.
     *
     * @param  LoggerInterface $logger    The logger
     * @param  string          $logFormat The log message format
     * @return $this
     */
    public function setLogger(LoggerInterface $logger, $logFormat = self::DEFAULT_LOG_FORMAT);

    /**
     * Sets the request method
     * 
     * Should be one of HttpMethods constants.
     * 
     * @param  string $method The request method
     * @return mixed
     * @throws Exceptions\InvalidArgumentException If $method is not one of the HttpMethods constants
     */
    public function setMethod($method);

    /**
     * Sets the get/post data
     *
     * How the data is handled depends on the request method, and the data type.
     * If data is an array, it will be passed through the http_build_query() function, and the return value will be
     * appended to the request url for GET request, and set as raw data for POST request. If data is a string, it will
     * be appended as-is to the request url for GET requests, and used as-is for POST requests.
     * 
     * The request content type will be automatically set to "multipart/form-data" if $data is an array, and the
     * request method is POST.
     * 
     * To post a file, prepend a filename with @ and use the full path, eg "@/home/headz/image.jpg".
     * 
     * Example:
     * ```php
     * $http = new WebClient(HTTP::METHOD_GET);
     * $http->setData("name=headz&job=programmer");
     * $http->request("http://example.com");
     * 
     * // The request will be made using the url "http://example.com?name=headz&job=programmer".
     *
     * $http = new WebClient(HTTP::METHOD_GET);
     * $http->setData(["name" => "headz", "job" => "programmer"]);
     * $http->request("http://example.com");
     *
     * // The request will be made using the url "http://example.com?name=headz&job=programmer".
     * 
     * $http = new WebClient(HTTP::METHOD_POST);
     * $http->setData("{'name':'headz', 'job':'programmer'}");
     * $http->request("http://example.com");
     *
     * // The request will be made using the url "http://example.com" with the raw post
     * // data "{'name':'headz', 'job':'programmer'}".
     * 
     * $http = new WebClient(HTTP::METHOD_POST);
     * $http->setData(["name" => "headz", "job" => "programmer"]);
     * $http->request("http://example.com");
     * 
     * // The request will be made using the url "http://example.com", the content type "multipart/form-data", and
     * // the POST data "name=headz&job=programmer".
     * ```
     * 
     * @param  string|array $data The get/post data
     * @return $this
     */
    public function setData($data);

    /**
     * Sets the content type
     *
     * Adds a header that will be sent with the request. The header may be specified as a key/value pair, or you may
     * the $header argument may include both name and value, "Location: http://example.com".
     * 
     * Example:
     * ```php
     * $http = new WebClient();
     * $http->addHeader("Content-Type", "text/html");
     * // Or like this.
     * $http->addHeader("Content-Type: text/html");
     * ```
     * 
     * @param  string $header The header name
     * @param  string $value  The header value
     * @return $this
     */
    public function addHeader($header, $value = null);

    /**
     * Sets the user agent value
     * 
     * @param  string $userAgent The user agent
     * @return $this
     */
    public function setUserAgent($userAgent);

    /**
     * Sets the basic auth username and password
     *
     * @param  string $user The username
     * @param  string $pass The password
     * @return $this
     */
    public function setBasicAuth($user, $pass);

    /**
     * Sends a request to an http server and returns the response
     *
     * @param  string $url The server url
     * @return WebResponse
     * @throws Exceptions\WebException If the request generates an error
     */
    public function request($url);

    /**
     * Perform an http GET request on the given url
     *
     * This method is a shortcut from writing the following code:
     * ```php
     * $web = new WebClient();
     * $web->setMethod(HttpMethods::GET);
     * $web->request($url);
     * ```
     *
     * @param  string $url The server url
     * @return WebResponse
     */
    public function get($url);

    /**
     * Perform an http POST request on the given url
     *
     * This method is a shortcut from writing the following code:
     * ```php
     * $web = new WebClient();
     * $web->setMethod(HttpMethods::POST);
     * $web->setData($data);
     * $web->request($url);
     * ```
     *
     * @param  string $url  The server url
     * @param  mixed  $data The post data
     * @return WebResponse
     */
    public function post($url, $data);
    
    /**
     * Returns useful debugging information about the last request made
     * 
     * The return value *may* contain one or more of the following keys:
     * ```
     * "url"
     * "content_type"
     * "http_code"
     * "header_size"
     * "request_size"
     * "filetime"
     * "ssl_verify_result"
     * "redirect_count"
     * "total_time"
     * "namelookup_time"
     * "connect_time"
     * "pretransfer_time"
     * "size_upload"
     * "size_download"
     * "speed_download"
     * "speed_upload"
     * "download_content_length"
     * "upload_content_length"
     * "starttransfer_time"
     * "redirect_time"
     * "redirect_url"
     * "primary_ip"
     * "certinfo"
     * "primary_port"
     * "local_ip"
     * "local_port"
     * ```
     * 
     * @return array
     */
    public function getInformation();
} 