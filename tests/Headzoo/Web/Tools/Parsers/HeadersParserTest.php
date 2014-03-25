<?php
use Headzoo\Web\Tools\Parsers\Headers;

class HeadersParserTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The test fixture 
     * @var Headers
     */
    protected $parser;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->parser = new Headers();
    }

    /**
     * @covers Headzoo\Web\Tools\Parsers\Headers::parse
     */
    public function testParse_Request()
    {
        $headers = <<< HEADERS
GET / HTTP/1.1
Authorization: Basic dGVzdF91c2VyOnRlc3RfcGFzcw==
User-Agent: headzoo/web-tools
Host: www.google.com
Accept: */*
Content-Type: text/html
HEADERS;

        $actual = $this->parser->parse($headers, $options);
        $this->assertEquals(
            "GET / HTTP/1.1",
            $options
        );
        $this->assertArrayHasKey(
            "User-Agent",
            $actual
        );
        $this->assertEquals(
            "headzoo/web-tools",
            $actual["User-Agent"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\Parsers\Headers::parse
     */
    public function testParse_Response()
    {
        $headers = <<< HEADERS
HTTP/1.1 200 OK
Date: Mon, 24 Mar 2014 19:06:05 GMT
Content-Type: text/html; charset=utf-8
Cache-Control: store, no-cache, must-revalidate
Content-Encoding: gzip
HEADERS;

        $actual = $this->parser->parse($headers, $options);
        $this->assertEquals(
            "HTTP/1.1 200 OK",
            $options
        );
        $this->assertArrayHasKey(
            "Content-Type",
            $actual
        );
        $this->assertEquals(
            "text/html; charset=utf-8",
            $actual["Content-Type"]
        );
    }
}
