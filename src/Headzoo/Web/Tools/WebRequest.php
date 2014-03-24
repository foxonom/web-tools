<?php
namespace Headzoo\Web\Tools;
use InvalidArgumentException;

/**
 * Represents a client http request.
 */
class WebRequest
{
    /**
     * The request data
     * @var array
     */
    protected $data = [
        "method"  => null,
        "version" => null,
        "host"    => null,
        "path"    => null,
        "headers" => [],
        "body"    => null,
        "params"  => [],
        "files"   => []
    ];

    /**
     * Constructor
     * 
     * @param array $data The request data
     * @throws InvalidArgumentException When any of the request values are empty except the body, params, and files
     */
    public function __construct(array $data)
    {
        foreach($this->data as $key => $value) {
            if (empty($data[$key]) && "body" !== $key && "params" !== $key && "files" !== $key) {
                throw new InvalidArgumentException(
                    "The data value for key '{$key}' cannot be empty."
                );
            }
        }
        $this->data = $data;
    }

    /**
     * Returns the http version, eg "HTTP/1.1"
     * 
     * @return string
     */
    public function getVersion()
    {
        return $this->data["version"];
    }
    
    /**
     * Returns the request method
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->data["method"];
    }

    /**
     * Returns the requested host
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->data["host"];
    }

    /**
     * Returns the requested path
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->data["path"];
    }

    /**
     * Returns the request headers
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->data["headers"];
    }

    /**
     * Returns the request body
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->data["body"];
    }

    /**
     * Returns the get/post parameters
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->data["params"];
    }

    /**
     * Returns uploaded files
     * 
     * @return array
     */
    public function getFiles()
    {
        return $this->data["files"];
    }
} 