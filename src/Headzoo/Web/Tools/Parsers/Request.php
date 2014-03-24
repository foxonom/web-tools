<?php
namespace Headzoo\Web\Tools\Parsers;
use Headzoo\Web\Tools\HttpMethods;
use Headzoo\Web\Tools\WebRequest;

/**
 * Parses raw client requests into individual components.
 */
class Request
    implements RequestInterface
{
    /**
     * Used to parse the request headers
     * @var HeadersInterface
     */
    protected $headerParser;

    /**
     * Constructor
     * 
     * @param HeadersInterface $headersParser Object used to parse request headers
     */
    public function __construct(HeadersInterface $headersParser = null)
    {
        if (null !== $headersParser) {
            $this->setHeadersParser($headersParser);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadersParser(HeadersInterface $headersParser)
    {
        $this->headerParser = $headersParser;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadersParser()
    {
        if (null === $this->headerParser) {
            $this->headerParser = new Headers();
        }
        return $this->headerParser;
    }

    /**
     * {@inheritDoc}
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

        $options          = null;
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
        
        return new WebRequest($data);
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