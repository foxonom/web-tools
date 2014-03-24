<?php
use Headzoo\Web\Tools\WebServer;
use Headzoo\Web\Tools\HttpRequest;

class WebServerTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRequest
     */
    public $request = null;
    
    /**
     * The test fixture
     * @var WebServer
     */
    protected $server;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->server = new WebServer(__DIR__);
    }

    /**
     * @covers Headzoo\Web\Tools\WebServer::start
     */
    public function testStart()
    {
        $this->server->setCallback(function(HttpRequest $request) {
            $this->request = $request;
            return "Hello, World!";
        });
        $this->server->start();
        $this->assertNotNull($this->request);
        $this->assertEquals(
            "/",
            $this->request->getPath()
        );
    }
}
