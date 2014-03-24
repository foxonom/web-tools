<?php
namespace Headzoo\Web\Tools\Parsers;

/**
 * Interface for classes which can parse raw request/response http headers.
 */
interface HeadersInterface
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
    public function parse($headers, &$options = null);
} 