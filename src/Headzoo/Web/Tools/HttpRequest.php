<?php
namespace Headzoo\Web\Tools;
use InvalidArgumentException;

/**
 * Represents a client http request.
 */
class HttpRequest
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
        "body"    => null
    ];

    /**
     * The get/post parameters
     * @var array
     */
    protected $params;

    /**
     * Data for uploaded files
     * @var array
     */
    protected $files;

    /**
     * Constructor
     * 
     * @param array $data The request data
     * @throws InvalidArgumentException When any of the request values are empty except the body
     */
    public function __construct(array $data)
    {
        foreach($this->data as $key => $value) {
            if (empty($data[$key]) && "body" !== $key) {
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
        if (null === $this->params) {
            $this->parseBody();
        }
        return $this->params;
    }

    /**
     * Returns uploaded files
     * 
     * @return array
     */
    public function getFiles()
    {
        if (null === $this->params) {
            $this->parseBody();
        }
        return $this->files;
    }

    /**
     * Parse raw http request body
     */
    protected function parseBody()
    {
        $this->params = [];
        $this->files  = [];

        if (!empty($this->data["body"]) && !empty($this->data["headers"]["Content-Type"])) {
            $matched = preg_match("/boundary=(.*)$/", $this->data["headers"]["Content-Type"], $matches);
            if (!$matched) {
                parse_str(urldecode($this->data["body"]), $this->params);
            } else {
                $boundary = $matches[1];
                $blocks   = preg_split("/-+$boundary/", $this->data["body"]);
                array_pop($blocks);

                foreach ($blocks as $block) {
                    if (!empty($block)) {
                        if (strpos($block, "application/octet-stream") !== false) {
                            preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                            $this->files[$matches[1]] = $matches[2];
                        } else {
                            preg_match("/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s", $block, $matches);
                            $this->params[$matches[1]] = $matches[2];
                        }
                    }
                }
            }
        }
    }
} 