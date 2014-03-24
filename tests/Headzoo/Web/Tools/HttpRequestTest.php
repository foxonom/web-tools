<?php
use Headzoo\Web\Tools\HttpRequest;

class HttpRequestTest
    extends PHPUnit_Framework_TestCase
{
    protected $data;
    
    /**
     * The test fixture
     * @var HttpRequest
     */
    protected $fixture;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->data = [
            "version" => "HTTP/1.1",
            "method"  => "GET",
            "host"    => "localhost:8888",
            "path"    => "/",
            "headers" => [
                "Connection" => "keep-alive",
                "User-Agent" => "Mozilla/5.0"
            ],
            "body"    => null
        ];
        $this->fixture = new HttpRequest($this->data);
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequest::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstruct_InvalidArgumentException()
    {
        $data = [
            "method" => "GET",
            "host"   => "",
            "path"   => "/",
            "headers" => [
                "Connection" => "keep-alive",
                "User-Agent" => "Mozilla/5.0"
            ],
            "body"    => null,
            "params"  => [],
            "files"   => []
        ];
        new HttpRequest($data);
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequest::getVersion
     */
    public function testGetVersion()
    {
        $this->assertEquals(
            $this->data["version"],
            $this->fixture->getVersion()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequest::getMethod
     */
    public function testGetMethod()
    {
        $this->assertEquals(
            $this->data["method"],
            $this->fixture->getMethod()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequest::getHost
     */
    public function testGetHost()
    {
        $this->assertEquals(
            $this->data["host"],
            $this->fixture->getHost()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequest::getPath
     */
    public function testGetPath()
    {
        $this->assertEquals(
            $this->data["path"],
            $this->fixture->getPath()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequest::getHeaders
     */
    public function testGetHeaders()
    {
        $this->assertEquals(
            $this->data["headers"],
            $this->fixture->getHeaders()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequest::getBody
     */
    public function testGetBody()
    {
        $this->assertEquals(
            $this->data["body"],
            $this->fixture->getBody()
        );
    }
}
