<?php
namespace Headzoo\Web\Tools;

/**
 * Represents a client http request.
 */
class WebRequest
    extends AbstractHttp
{
    /**
     * The request values
     * @var array
     */
    protected $values = [
        "time"    => null,
        "method"  => null,
        "version" => null,
        "host"    => null,
        "path"    => null,
        "body"    => null,
        "headers" => [],
        "params"  => [],
        "files"   => []
    ];

    /**
     * List of required request/response values
     * @var array
     */
    protected $required = [
        "time",
        "method",
        "version",
        "host",
        "path"
    ];

    /**
     * Returns a unix timestamp recorded at the time the request was made
     * 
     * @return int
     */
    public function getTime()
    {
        return $this->values["time"];
    }
    
    /**
     * Returns the http version, eg "HTTP/1.1"
     * 
     * @return string
     */
    public function getVersion()
    {
        return $this->values["version"];
    }
    
    /**
     * Returns the request method
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->values["method"];
    }

    /**
     * Returns the requested host
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->values["host"];
    }

    /**
     * Returns the requested path
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->values["path"];
    }

    /**
     * Returns the request headers
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->values["headers"];
    }

    /**
     * Returns the request body
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->values["body"];
    }

    /**
     * Returns the get/post parameters
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->values["params"];
    }

    /**
     * Returns uploaded files
     * 
     * @return array
     */
    public function getFiles()
    {
        return $this->values["files"];
    }
} 