<?php
use Headzoo\Web\Tools\WebResponse;

class WebResponseTest
    extends PHPUnit_Framework_TestCase
{
    protected $values;
    
    /**
     * The test fixture
     * @var WebResponse
     */
    protected $response;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->values = [
            "time"    => time(),
            "version" => "HTTP/1.1",
            "code"    => 200,
            "method"  => "GET",
            "body"    => "Welcome to my site!",
            "headers" => [
                "Content-Type"     => "text/html; charset=utf-8",
                "Content-Language" => "en",
                "Content-Encoding" => "gzip"
            ],
            "info" => [
                "url"      => "http://mysite.com",
                "local_ip" => "127.0.0.1"
            ]
        ];
        $this->response = new WebResponse($this->values);
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::__construct
     * @expectedException Headzoo\Utilities\Exceptions\ValidationFailedException
     */
    public function testConstruct_InvalidArgumentException()
    {
        $this->values = [
            "time"    => time(),
            "method"  => "GET",
            "body"    => "Welcome to my site!",
            "headers" => [
                "Content-Type"     => "text/html; charset=utf-8",
                "Content-Language" => "en",
                "Content-Encoding" => "gzip"
            ],
            "info" => [
                "url"      => "http://mysite.com",
                "local_ip" => "127.0.0.1"
            ]
        ];
        $this->response = new WebResponse($this->values);
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::getTime
     */
    public function testGetTime()
    {
        $this->assertEquals(
            $this->values["time"],
            $this->response->getTime()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::getVersion
     */
    public function testGetVersion()
    {
        $this->assertEquals(
            $this->values["version"],
            $this->response->getVersion()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::getMethod
     */
    public function testGetMethod()
    {
        $this->assertEquals(
            $this->values["method"],
            $this->response->getMethod()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::getCode
     */
    public function testGetCode()
    {
        $this->assertEquals(
            $this->values["code"],
            $this->response->getCode()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::getHeaders
     */
    public function testGetHeaders()
    {
        $this->assertEquals(
            $this->values["headers"],
            $this->response->getHeaders()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::getBody
     */
    public function testGetBody()
    {
        $this->assertEquals(
            $this->values["body"],
            $this->response->getBody()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebResponse::getInformation
     */
    public function testGetInformation()
    {
        $this->assertEquals(
            $this->values["info"],
            $this->response->getInformation()
        );
    }
}
