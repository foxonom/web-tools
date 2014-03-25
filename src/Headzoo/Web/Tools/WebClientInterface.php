<?php
namespace Headzoo\Web\Tools;

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
     * Sends the request and returns the response
     *
     * @param  string $url The url to request
     * @return string
     * @throws Exceptions\WebException If the request generates an error
     */
    public function request($url);

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
     * Returns the status code returned by the server
     *
     * The status code will be 0 if the request fails.
     * 
     * @return int
     */
    public function getStatusCode();

    /**
     * Returns the headers that were sent with the request
     * 
     * The headers will not be available if the request fails, and this method will return
     * an empty array.
     * 
     * @return array
     */
    public function getRequestHeaders();

    /**
     * Returns information about the last request
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