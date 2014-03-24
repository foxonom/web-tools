<?php
namespace Headzoo\Web\Tools;

/**
 * Parses request/response headers into an array.
 */
class HttpHeadersParser
    implements HttpHeadersParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parse($headers, &$options = null)
    {
        $methods = HttpMethods::getValues();
        $methods = join("|", $methods);
        $regex   = "~^({$methods})\\b~i";
        
        $parsed  = [];
        $lines = preg_split("/\\R/", $headers);
        foreach($lines as $line) {
            if (preg_match($regex, $line)) {
                $options = $line;
            } else if (preg_match("~^HTTP/~", $line)) {
                $options = $line;
            } else if (!empty($line)) {
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