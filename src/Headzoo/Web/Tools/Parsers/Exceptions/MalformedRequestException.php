<?php
namespace Headzoo\Web\Tools\Parsers\Exceptions;
use Headzoo\Web\Tools\Exceptions\WebException;

/**
 * Thrown by http request parsers when the request is not formatted correctly.
 */
class MalformedRequestException
    extends WebException {}