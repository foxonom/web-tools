<?php
namespace Headzoo\Web\Tools\Parsers;
use Headzoo\Web\Tools\WebRequest;

/**
 * Interface for classes which parse raw http requests.
 */
interface RequestInterface
{
    /**
     * Sets the object which will be used to parse request headers
     *
     * @param  HeadersInterface $headersParser The headers parser
     * @return $this
     */
    public function setHeadersParser(HeadersInterface $headersParser);

    /**
     * Returns the object which will be used to parse request headers
     *
     * @return HeadersInterface
     */
    public function getHeadersParser();

    /**
     * Parses the raw request data
     *
     * @param  string $request The raw request data
     * @return WebRequest
     * @throws Exceptions\MalformedRequestException When the request is malformed and cannot be parsed
     */
    public function parse($request);
} 