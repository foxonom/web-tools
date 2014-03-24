<?php
use Headzoo\Web\Tools\HttpRequestParser;

class HttpRequestParserTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The test fixture
     * @var HttpRequestParser
     */
    protected $parser;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->parser = new HttpRequestParser();
    }

    /**
     * @covers Headzoo\Web\Tools\HttpRequestParser::parse
     */
    public function testParse()
    {
        $request = <<< REQ
GET / HTTP/1.1
Host: localhost:8888
Connection: keep-alive
Cache-Control: max-age=0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36
Accept-Encoding: gzip,deflate,sdch
Accept-Language: en-US,en;q=0.8,de;q=0.6,ms;q=0.4,sl;q=0.2,sr;q=0.2


REQ;

        $request = $this->parser->parse($request);
        $this->assertInstanceOf(
            'Headzoo\Web\Tools\HttpRequest',
            $request
        );
        $this->assertEquals(
            "GET",
            $request->getMethod()
        );
        $this->assertEquals(
            "HTTP/1.1",
            $request->getVersion()
        );
        $this->assertEquals(
            "/",
            $request->getPath()
        );
        $this->assertEquals(
            "localhost:8888",
            $request->getHost()
        );
        $this->assertEquals(
            "",
            $request->getBody()
        );
        $this->assertArrayHasKey(
            "Cache-Control",
            $request->getHeaders()
        );
    }
}
