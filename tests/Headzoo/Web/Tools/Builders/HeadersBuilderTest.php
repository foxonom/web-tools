<?php
use Headzoo\Web\Tools\Builders\Headers;

class HeadersBuilderTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The test fixture
     * @var Headers
     */
    protected $builder;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->builder = new Headers();
    }

    /**
     * @covers Headzoo\Web\Tools\Builders\Headers::build
     */
    public function testBuild()
    {
        $headers = [
            "Content-Type"    => "text/html",
            "Cache-Control"   => "no-cache",
            "Accept: */*",
            "Accept-Encoding: gzip,deflate",
            "Accept-Language" => "en-US,en",
            "X-Forwarded-For" => "127.0.0.1"
        ];
        $expected = <<< RAW
Content-Type: text/html
Cache-Control: no-cache
Accept: */*
Accept-Encoding: gzip,deflate
Accept-Language: en-US,en
X-Forwarded-For: 127.0.0.1
RAW;
        
        $expected = trim(preg_replace("/\\R/", Headers::NEWLINE, $expected)) . Headers::NEWLINE;
        $this->assertEquals(
            $expected,
            $this->builder->build($headers)
        );
    }

    /**
     * @covers Headzoo\Web\Tools\Builders\Headers::build
     */
    public function testBuild_StripX()
    {
        $headers = [
            "Content-Type"    => "text/html",
            "Cache-Control"   => "no-cache",
            "Accept: */*",
            "Accept-Encoding: gzip,deflate",
            "Accept-Language" => "en-US,en",
            "X-Forwarded-For" => "127.0.0.1"
        ];
        $expected = <<< RAW
Content-Type: text/html
Cache-Control: no-cache
Accept: */*
Accept-Encoding: gzip,deflate
Accept-Language: en-US,en
Forwarded-For: 127.0.0.1
RAW;

        $this->builder->setStripX(true);
        $expected = trim(preg_replace("/\\R/", Headers::NEWLINE, $expected)) . Headers::NEWLINE;
        $this->assertEquals(
            $expected,
            $this->builder->build($headers)
        );
    }

    /**
     * @covers Headzoo\Web\Tools\Builders\Headers::build
     * @expectedException Headzoo\Web\Tools\Builders\Exceptions\BuildException
     */
    public function testBuild_InvalidArgument()
    {
        $headers = [];
        for($i = 0; $i < Headers::MAX_HEADERS + 1; $i++) {
            $headers[] = "Content-Type: text/plain";
        }
        $this->builder->build($headers);
    }
}
