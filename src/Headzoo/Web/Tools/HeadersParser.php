<?php
namespace Headzoo\Web\Tools;

/**
 * Parses request/response headers into an array.
 */
class HeadersParser
{
    /**
     * Parses raw http headers, and returns them as an array
     * 
     * The $options argument will be populated with the request/response portion of the headers, eg "GET / HTTP/1.1"
     * or "HTTP/1.1 200 OK" if found.
     * 
     * The header keys are normalized to ensure they are always formatted "Camel-Case", eg "Content-Type".
     * 
     * @param  string $headers The raw http headers
     * @param  null   $options Populated with http options
     * @return array
     */
    public function parse($headers, &$options = null)
    {
        $methods = HttpMethods::getValues();
        $methods = join("|", $methods);
        $regex   = "/^({$methods})/i";
        
        $parsed  = [];
        $lines = preg_split("/\\R/", $headers);
        foreach($lines as $line) {
            if (preg_match($regex, $line)) {
                $options = $line;
            } else if (preg_match("~^HTTP/~", $line)) {
                $options = $line;
            } else {
                list($name, $value) = preg_split("/:\\S*/", $line, 2);
                $name  = str_replace("-", " ", $name);
                $name  = ucwords(strtolower($name));
                $name  = str_replace(" ", "-", $name);
                $value = trim($value);
                $parsed[$name] = $value;
            }
        }
        
        return $parsed;
    }
} 