<?php
use Headzoo\Web\Tools\WebClient;
use Headzoo\Web\Tools\WebResponse;
use Headzoo\Web\Tools\HttpMethods;

class WebClientTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The test url
     */
    const TEST_URL = "http://localhost/web-tools/tests/index.php";

    /**
     * Actual requested url
     * @var string
     */
    protected $url;
    
    /**
     * The test fixture
     * @var WebClient
     */
    protected $web;

    /**
     * @var WebResponse
     */
    protected $response;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->web = new WebClient();
        $this->web->addHeader("X-Testing-Header", "Found");
        $this->web->addHeader("X-Request-Type: unit-testing");
    }

    /**
     * Called before each test exits, and before tearDown()
     */
    public function assertPostConditions()
    {
        if (!$this->getExpectedException()) {
            $this->assertEquals(
                200,
                $this->response->getCode()
            );
            $this->assertNotEmpty($this->response->getTime());
            $this->assertEquals(
                "HTTP/1.1",
                $this->response->getVersion()
            );
            $this->assertContains(
                $this->url,
                $this->response->getInformation()["url"]
            );
            $headers = $this->response->getHeaders();
            $this->assertEquals(
                "Howdy",
                $headers["X-Test-Response"]
            );
            $this->assertEquals(
                "unit-testing",
                $headers["X-Request-Type"]
            );
        }
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get()
    {
        $actual = $this->request();
        $this->assertContains(
            WebClient::DEFAULT_CONTENT_TYPE,
            $actual["CONTENT_TYPE"]
        );
        $this->assertEquals(
            parse_url(self::TEST_URL, PHP_URL_PATH),
            $actual["REQUEST_URI"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get_Multi()
    {
        $time = time();
        $this->web->addHeader("X-Start-Time", $time);

        $this->request();
        $this->assertEquals(
            $time,
            $this->response->getHeaders()["X-Start-Time"]
        );

        $this->request("?action=list");
        $this->assertEquals(
            $time,
            $this->response->getHeaders()["X-Start-Time"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get_Data_Array()
    {
        $this->web->setData(["name" => "Sean", "job" => "programmer"]);
        $actual = $this->request();

        $this->assertEquals(
            "Sean",
            $actual["GET"]["name"]
        );
        $this->assertEquals(
            "programmer",
            $actual["GET"]["job"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get_Data_Array_Append()
    {
        $this->web->setData(["name" => "Sean", "job" => "programmer"]);
        $actual = $this->request("?action=list");

        $this->assertEquals(
            "list",
            $actual["GET"]["action"]
        );
        $this->assertEquals(
            "Sean",
            $actual["GET"]["name"]
        );
        $this->assertEquals(
            "programmer",
            $actual["GET"]["job"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get_Data_String()
    {
        $this->web->setData("name=Sean&job=programmer");
        $actual = $this->request();

        $this->assertEquals(
            "Sean",
            $actual["GET"]["name"]
        );
        $this->assertEquals(
            "programmer",
            $actual["GET"]["job"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get_Data_String_Append()
    {
        $this->web->setData("name=Sean&job=programmer");
        $actual = $this->request("?action=list");

        $this->assertEquals(
            "list",
            $actual["GET"]["action"]
        );
        $this->assertEquals(
            "Sean",
            $actual["GET"]["name"]
        );
        $this->assertEquals(
            "programmer",
            $actual["GET"]["job"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Post_Data_Array()
    {
        $this->web
            ->setMethod(HttpMethods::POST)
            ->setData(["name" => "Sean", "job" => "programmer"]);
        $actual = $this->request();

        $this->assertContains(
            "multipart/form-data",
            $actual["CONTENT_TYPE"]
        );
        $this->assertEquals(
            "Sean",
            $actual["POST"]["name"]
        );
        $this->assertEquals(
            "programmer",
            $actual["POST"]["job"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Post_Data_String()
    {
        $this->web
            ->setMethod(HttpMethods::POST)
            ->setData("name=Sean&job=programmer");
        $actual = $this->request();

        $this->assertContains(
            "application/x-www-form-urlencoded",
            $actual["CONTENT_TYPE"]
        );
        $this->assertEquals(
            "Sean",
            $actual["POST"]["name"]
        );
        $this->assertEquals(
            "programmer",
            $actual["POST"]["job"]
        );
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     * @expectedException Headzoo\Web\Tools\Exceptions\WebException
     */
    public function testRequest_Post_NoData()
    {
        $this->web->setMethod(HttpMethods::POST);
        $this->request();
    }

    /**
     * @covers Headzoo\Web\Tools\WebClient::request
     */
    public function testRequest_Get_Auth()
    {
        $this->markTestSkipped();
        $this->web->setBasicAuth("test_user", "test_pass");
        $this->request();
        $this->assertArrayHasKey(
            "Authorization",
            $this->response->getHeaders()
        );
    }

    /**
     * Called $this->web->request(), and decode the json response
     * 
     * @param  string $query Query string to append to the test url
     * @return array
     * @throws Exception
     */
    protected function request($query = null)
    {
        $this->url = self::TEST_URL;
        if (null !== $query) {
            $query = ltrim($query, "?");
            $this->url .= "?{$query}";
        }
        $this->response = $this->web->request($this->url);
        $array = json_decode($this->response->getBody(), true);
        if (!$array) {
            throw new Exception("Testing server did not return json encoded data.");
        }
        
        return $array;
    }
}
