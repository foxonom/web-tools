<?php
namespace Headzoo\Web\Tools;
use ReflectionClass;

/**
 * Enum type class containing the types of request methods.
 * 
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
 */
class HttpMethods
{
    /**
     * The GET method means retrieve whatever information (in the form of an entity) is identified by the Request-URI.
     */
    const GET = "GET";

    /**
     * The POST method is used to request that the origin server accept the entity enclosed in the request as a new
     * subordinate of the resource identified by the Request-URI in the Request-Line.
     */
    const POST = "POST";

    /**
     * The PUT method requests that the enclosed entity be stored under the supplied Request-URI.
     */
    const PUT = "PUT";

    /**
     * The DELETE method requests that the origin server delete the resource identified by the Request-URI.
     */
    const DELETE = "DELETE";

    /**
     * The HEAD method is identical to GET except that the server MUST NOT return a message-body in the response.
     */
    const HEAD = "HEAD";

    /**
     * The OPTIONS method represents a request for information about the communication options available on the
     * request/response chain identified by the Request-URI.
     */
    const OPTIONS = "OPTIONS";

    /**
     * The TRACE method is used to invoke a remote, application-layer loop- back of the request message.
     */
    const TRACE = "TRACE";

    /**
     * This specification reserves the method name CONNECT for use with a proxy that can dynamically switch to
     * being a tunnel.
     */
    const CONNECT = "CONNECT";

    /**
     * Returns the constants in the class as an arrray of key/value pairs
     * 
     * @return array
     */
    public static function getConstants()
    {
        $ref = new ReflectionClass(get_called_class());
        return $ref->getConstants();
    }

    /**
     * Returns the names of each constant in the class
     * 
     * @return array
     */
    public static function getNames()
    {
        return array_keys(self::getConstants());
    }

    /**
     * Returns the values of the constants found in the class
     * 
     * @return array
     */
    public static function getValues()
    {
        return array_values(self::getConstants());
    }
} 