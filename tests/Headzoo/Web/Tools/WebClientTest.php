<?php
use Headzoo\Web\Tools\WebClient;

class WebClientTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The test fixture
     * @var WebClient
     */
    protected $web;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->web = new WebClient();
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get()
    {
        $actual = $this->web->request("http://www.google.com/");
        $this->assertContains("google.com", $actual);
        $this->assertEquals(200, $this->web->getStatusCode());
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Post()
    {
        $this->web->setMethod(WebClient::METHOD_POST);
        $actual = $this->web->request("http://localhost:8888/");
        $this->assertContains("Hello, World!", $actual);
        $this->assertEquals(200, $this->web->getStatusCode());
    }
}
