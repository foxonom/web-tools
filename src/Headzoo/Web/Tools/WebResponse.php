<?php
namespace Headzoo\Web\Tools;

/**
 * Represents a client http response.
 */
class WebResponse
    extends AbstractHttp
{
    /**
     * The response data
     * @var array
     */
    protected $data = [
        "time"    => null,
        "method"  => null,
        "version" => null,
        "code"    => null,
        "body"    => null,
        "info"    => [],
        "headers" => [],
    ];

    /**
     * List of required request/response values
     * @var array
     */
    protected $required = [
        "time",
        "method",
        "version",
        "code",
        "info"
    ];

    /**
     * Returns a unix timestamp recorded at the time the response was received
     *
     * @return int
     */
    public function getTime()
    {
        return $this->values["time"];
    }

    /**
     * Returns the http version the client responded with, eg "HTTP/1.1"
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->values["version"];
    }

    /**
     * Returns the method used to make the request
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->values["method"];
    }

    /**
     * Returns the response http status code
     * 
     * @return int
     */
    public function getCode()
    {
        return $this->values["code"];
    }

    /**
     * Returns the response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->values["headers"];
    }

    /**
     * Returns the response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->values["body"];
    }

    /**
     * Returns information about the request
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
    public function getInformation()
    {
        return $this->values["info"];
    }
}