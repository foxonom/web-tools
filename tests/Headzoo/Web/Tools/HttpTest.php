<?php
use Headzoo\Web\Tools\AbstractHttp;

class HttpTestClass
    extends AbstractHttp
{
    public function __construct() {}
    public function setRequired(array $required)
    {
        $this->required = $required;
    }
}

class HttpTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Headzoo\Web\Tools\Http::getValidator
     */
    public function testGetValidator()
    {
        $values = [
            "time"    => time(),
            "version" => "HTTP/1.1",
            "method"  => "GET",
            "body"    => null
        ];
        $required = [
            "time",
            "version",
            "method"
        ];
        $http = new HttpTestClass();
        $http->setRequired($required);
        $this->assertSame(
            $http,
            $http->setValues($values)
        );
    }

    /**
     * @covers Headzoo\Web\Tools\Http::getValidator
     * @expectedException Headzoo\Utilities\Exceptions\ValidationFailedException
     */
    public function testGetValidator_Missing()
    {
        $values = [
            "time"    => time(),
            "method"  => "GET",
            "body"    => null
        ];
        $required = [
            "time",
            "version",
            "method"
        ];
        $http = new HttpTestClass();
        $http->setRequired($required);
        $http->setValues($values);
    }
}
