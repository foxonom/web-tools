<?php
namespace Headzoo\Web\Tools;

/**
 * Parses client requests into individual components.
 */
class HttpRequestParser
{
    /**
     * Used to parse the request headers
     * @var HttpHeadersParserInterface
     */
    protected $headerParser;

    /**
     * Constructor
     * 
     * @param HttpHeadersParserInterface $headersParser Object used to parse request headers
     */
    public function __construct(HttpHeadersParserInterface $headersParser = null)
    {
        if (null !== $headersParser) {
            $this->setHeadersParser($headersParser);
        }
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
     * Parses the raw request data
     * 
     * @param  string $request The request data
     * @return HttpRequest
     * @throws Exceptions\MalformedRequestException When the request is malformed and cannot be parsed
     */
    public function parse($request)
    {
        $parts = preg_split("/\\R\\R/", $request, 2);
        if (count($parts) < 2) {
            throw new Exceptions\MalformedRequestException(
                "Malformed HTTP request at headers and body."
            );
        }
        
        $data = [
            "method"  => null,
            "version" => null,
            "host"    => null,
            "path"    => null,
            "headers" => [],
            "body"    => trim($parts[1]),
            "params"  => [],
            "files"   => []
        ];

        $headersParser    = $this->getHeadersParser();
        $data["headers"]  = $headersParser->parse($parts[0], $options);
        if (empty($data["headers"]["Host"])) {
            throw new Exceptions\MalformedRequestException(
                "Malformed HTTP request at host."
            );
        }
        
        $data["host"]     = $data["headers"]["Host"];
        $options          = explode(" ", $options, 3);  
        $data["method"]   = array_shift($options);
        $data["path"]     = array_shift($options);
        $data["version"]  = array_shift($options);
        if (empty($data["method"]) || empty($data["path"]) || empty($data["version"])) {
            throw new Exceptions\MalformedRequestException(
                "Malformed HTTP request at options."
            );
        }
        
        if (HttpMethods::POST === $data["method"]) {
            $data = $this->parsePostBody($data);
        } else {
            $parts = parse_url($data["path"]);
            if (!empty($parts["path"])) {
                $data["path"] = $parts["path"];
            }
            if (!empty($parts["query"])) {
                parse_str($parts["query"], $data["params"]);
            }
        }
        
        return new HttpRequest($data);
    }

    /**
     * Parse raw http request body
     * 
     * @param  array $data The request data
     * @return array
     */
    protected function parsePostBody($data)
    {
        if (!empty($data["body"]) && !empty($data["headers"]["Content-Type"])) {
            $matched = preg_match("/boundary=(.*)$/", $data["headers"]["Content-Type"], $matches);
            if (!$matched) {
                parse_str(urldecode($data["body"]), $params);
            } else {
                $boundary = $matches[1];
                $blocks   = preg_split("/-+$boundary/", $data["body"]);
                array_pop($blocks);

                foreach ($blocks as $block) {
                    if (!empty($block)) {
                        if (strpos($block, "application/octet-stream") !== false) {
                            preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                            $data["files"][$matches[1]] = $matches[2];
                        } else {
                            preg_match("/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s", $block, $matches);
                            $data["params"][$matches[1]] = $matches[2];
                        }
                    }
                }
            }
        }
        
        return $data;
    }
} 