<?php
use Headzoo\Web\Tools\WebRequest;

class WebRequestTest
    extends PHPUnit_Framework_TestCase
{
    protected $data;
    
    /**
     * The test fixture
     * @var WebRequest
     */
    protected $request;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->data = [
            "time"    => time(),
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
        $this->request = new WebRequest($this->data);
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::__construct
     * @expectedException Headzoo\Utilities\Exceptions\ValidationFailedException
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
        new WebRequest($data);
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::getVersion
     */
    public function testGetVersion()
    {
        $this->assertEquals(
            $this->data["version"],
            $this->request->getVersion()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::getMethod
     */
    public function testGetMethod()
    {
        $this->assertEquals(
            $this->data["method"],
            $this->request->getMethod()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::getHost
     */
    public function testGetHost()
    {
        $this->assertEquals(
            $this->data["host"],
            $this->request->getHost()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::getPath
     */
    public function testGetPath()
    {
        $this->assertEquals(
            $this->data["path"],
            $this->request->getPath()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::getHeaders
     */
    public function testGetHeaders()
    {
        $this->assertEquals(
            $this->data["headers"],
            $this->request->getHeaders()
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::getBody
     */
    public function testGetBody()
    {
        $this->assertEquals(
            $this->data["body"],
            $this->request->getBody()
        );
    }
}
