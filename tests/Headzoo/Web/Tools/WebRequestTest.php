<?php
use Headzoo\Web\Tools\WebRequest;
use Headzoo\Web\Tools\WebServer;

class WebRequestTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The test fixture
     * @var WebRequest
     */
    protected $web;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->web = new WebRequest();
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::request
     */
    public function testRequest_Get()
    {
        $actual = $this->web->request("http://www.google.com/");
        $this->assertContains("google.com", $actual);
        $this->assertEquals(200, $this->web->getStatusCode());
    }

    /**
     * @covers Headzoo\Web\Tools\WebRequest::request
     */
    public function testRequest_Post()
    {
        $this->web->setMethod(WebRequest::METHOD_POST);
        $actual = $this->web->request("http://localhost:8888/");
        $this->assertContains("Hello, World!", $actual);
        $this->assertEquals(200, $this->web->getStatusCode());
    }
}
