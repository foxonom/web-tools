<?php
namespace Headzoo\Web\Tools\Builders;

/**
 * Interface for classes which build raw http headers.
 */
interface HeadersInterface
{
    /**
     * New line sequence
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec2.html#sec2.2
     */
    const NEWLINE = "\r\n";

    /**
     * Default value for removing "X-" prefixes
     */
    const DEFAULT_STRIP_X = false;

    /**
     * The maximum number of allowed header fields
     */
    const MAX_HEADERS = 100;
    
    /**
     * Returns whether the experimental "X-" prefix will be removed from header names
     *
     * @return bool
     */
    public function getStripX();

    /**
     * Sets whether the experimental "X-" prefix should be removed from header names
     *
     * @param  bool $stripX True to remove the prefix, false to leave it alone
     * @return $this
     */
    public function setStripX($stripX);
    
    /**
     * Converts an array of header values into a string of raw headers
     *
     * The input array may use key/value pairs, for example ["content-type" => "text/html"], or it may use
     * plain strings, for example ["content-type: text/html"]. Each field name will be passed through
     * HeadersInterface::normalize() to ensure proper formatting.
     * 
     * Example:
     * ```php
     * $headers = [
     *      "CONTENT_TYPE" => "text/html",
     *      "x-Forwarded-For" => "127.0.0.1",
     *      "Accept-Language: en-US,en",
     *      "accept-encoding: gzip,deflate"
     * ];
     * 
     * $builder = new Headers(true);
     * echo $builder->build($headers);
     * 
     * // Outputs:
     * // Content-Type: text/html
     * // Forwarded-For: 127.0.0.1
     * // Accept-Language: en-US,en
     * // Accept-Encoding: gzip,deflate
     * ```
     * 
     * @param  array $headers The header names and values
     * @return string
     * @throws Exceptions\BuildErrorException If the number of headers exceeds the value of HeadersInterface::MAX_HEADERS
     */
    public function build(array $headers);

    /**
     * Normalizes an array of header names
     *
     * Returns an array of normalized header names and values as key/value pairs. The input array may use
     * key/value pairs, for example ["content-type" => "text/html"], or it may use plain strings, for
     * example ["content-type: text/html"].
     *
     * Example:
     * ```php
     * // Note some headers in the array represented as key/value pairs, while others
     * // are full header values.
     * $headers = [
     *      "CONTENT_TYPE" => "text/html",
     *      "x-Forwarded-For" => "127.0.0.1",
     *      "Accept-Language: en-US,en",
     *      "accept-encoding: gzip,deflate"
     * ];
     *
     * $builder = new Headers(true);
     * $headers = $builder->normalize($headers);
     * print_r($headers);
     *
     * // The output has normalized names, and every header is represented by key/value pairs.
     * // The "X-" prefix has also been removed.
     * [
     *      "Content-Type"    => "text/html",
     *      "Forwarded-For"   => "127.0.0.1",
     *      "Accept-Language" => "en-US,en",
     *      "Accept-Encoding" => "gzip,deflate"
     * ]
     * ```
     *
     * @param  array $headers The header names and values
     * @return array
     */
    public function normalize(array $headers);
} 