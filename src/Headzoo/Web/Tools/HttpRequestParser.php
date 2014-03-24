<?php
namespace Headzoo\Web\Tools;
use UnexpectedValueException;

/**
 * Parses client requests into individual components.
 */
class HttpRequestParser
{
    /**
     * Parses the request data
     * 
     * @param string $request The request data
     * @throws UnexpectedValueException
     * @return HttpRequest
     */
    public function parse($request)
    {
        // Normalizing line feeds in case of buggy browsers, and splitting the headers
        // from the body.
        $request = preg_replace('~(*BSR_ANYCRLF)\R~', "\r\n", $request);
        $parts   = explode("\r\n\r\n", $request, 2);
        if (count($parts) < 2) {
            throw new UnexpectedValueException(
                "Malformed HTTP request at headers and body."
            );
        }
        
        $data = [
            "method"  => null,
            "version" => null,
            "host"    => null,
            "path"    => null,
            "headers" => [],
            "body"    => trim($parts[1])
        ];

        // Splitting up the headers, the first header must be the options, which must
        // be well formatted, eg "GET / HTTP/1.1".
        $parts[0]         = preg_split("/(\\r\\n)/", $parts[0]);
        $options          = explode(" ", array_shift($parts[0]), 3);
        $data["method"]   = array_shift($options);
        $data["path"]     = array_shift($options);
        $data["version"]  = array_shift($options);
        if ("GET" != $data["method"] && "POST" != $data["method"]) {
            throw new UnexpectedValueException(
                "Malformed HTTP request at options."
            );
        }
        
        // The Host header must be next.
        $data["host"] = array_shift($parts[0]);
        if (substr($data["host"], 0, 5) != "Host:") {
            throw new UnexpectedValueException(
                "Malformed HTTP request at host."
            );
        }
        $data["host"] = trim(explode(":", $data["host"], 2)[1]);

        // Parsing the headers, and normalizing the header names to follow the
        // format "Camel-Case", eg "Content-Type".
        foreach($parts[0] as $header) {
            list($name, $value) = preg_split("/:\\S*/", $header, 2);
            $name  = str_replace("-", " ", $name);
            $name  = ucwords(strtolower($name));
            $name  = str_replace(" ", "-", $name);
            $data["headers"][$name] = trim($value);
        }
        
        return new HttpRequest($data);
    }
} 