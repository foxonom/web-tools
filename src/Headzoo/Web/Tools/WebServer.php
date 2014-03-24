<?php
namespace Headzoo\Web\Tools;
use Headzoo\Utilities\Complete;

/**
 * Acts as a small http server capable of handling one request.
 * 
 * This class is designed for testing, and should never be used as an actual http server. It has very
 * little error checking, and no security checks.
 */
class WebServer
    implements WebServerInterface
{
    /**
     * The socket connection
     * @var resource
     */
    private $socket;

    /**
     * Currently connected client
     * @var resource
     */
    private $client;

    /**
     * Used to parse requests
     * @var Parsers\Request
     */
    private $httpParser;
    
    /**
     * The host name or ip to bind to
     * @var string
     */
    protected $host;

    /**
     * The port to listen on
     * @var int
     */
    protected $port;

    /**
     * Path to the root html directory
     * @var string
     */
    protected $dirRoot;

    /**
     * Default index file name
     * @var string
     */
    protected $index = self::DEFAULT_INDEX;

    /**
     * Request event callback
     * @var callable
     */
    protected $callback;

    /**
     * Constructor
     * 
     * @param string $dirRoot The root html directory
     * @param string $host    The host name or ip to bind to
     * @param int    $port    The port to listen on
     */
    public function __construct($dirRoot, $host = self::DEFAULT_HOST, $port = self::DEFAULT_PORT)
    {
        $this->dirRoot = realpath($dirRoot);
        $this->host    = $host;
        $this->port    = $port;
    }

    /**
     * Sets the object used to parse http requests
     * 
     * @param  Parsers\Request $httpParser The parser
     * @return $this
     */
    public function setHttpParser(Parsers\Request $httpParser)
    {
        $this->httpParser = $httpParser;
        return $this;
    }

    /**
     * Returns the object that will be used to parse http request
     * 
     * @return Parsers\Request
     */
    public function getHttpParser()
    {
        if (!$this->httpParser) {
            $this->httpParser = new Parsers\Request();
        }
        return $this->httpParser;
    }

    /**
     * Sets the default index file
     * 
     * @param  string $index The default index file
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Returns the default index file
     * 
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Sets a function/method which will handle requests
     * 
     * Rather than have the server handle the request, the callback will be called, and it will
     * receive the HttpRequest object as it's only argument. The callback should return the
     * content that should be sent to the client.
     * 
     * If the callback returns false or null, the request will continue to be handled normally. The callback should
     * throw instances of Exceptions\HttpStatusError to signal an error.
     * 
     * @param  callable $callback The function/method to handle requests
     * @return $this
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function start($single = true)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        if (!$this->socket) {
            $err = socket_last_error();
            throw new Exceptions\SocketException(
                socket_strerror($err),
                $err
            );
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $complete = Complete::factory(function() {
            if ($this->socket) {
                socket_close($this->socket);
            }
        });
        
        if (!socket_bind($this->socket, $this->host, $this->port) || !socket_listen($this->socket)) {
            $err = socket_last_error();
            throw new Exceptions\SocketException(
                socket_strerror($err),
                $err
            );
        }
        
        while(true) {
            $this->client = socket_accept($this->socket);
            if (!$this->client) {
                $err = socket_last_error();
                throw new Exceptions\SocketException(
                    socket_strerror($err),
                    $err
                );
            }
            
            $input = socket_read($this->client, 2045);
            if (false === $input) {
                $err = socket_last_error();
                throw new Exceptions\SocketException(
                    socket_strerror($err),
                    $err
                );
            }
            
            $headersSent = false;
            $parser   = $this->getHttpParser();
            $request  = $parser->parse($input);
            $response = "";
            
            try {
                if ($this->callback) {
                    $response = call_user_func($this->callback, $request);
                }
                if (false === $response || null === $response) {
                    $response = $this->getFile($request);
                }
            } catch (Exceptions\HttpStatusError $e) {
                $this->sendResponseHeadersToClient(
                    $request,
                    $e->getCode(), 
                    $e->getMessage()
                );
                $headersSent = true;
            }
            if (!$headersSent) {
                $this->sendResponseHeadersToClient($request, 200, "OK");
                $this->sendToClient($response);
            }
            
            if ($single) {
                break;
            }
        }
    }

    /**
     * Sends the response headers through the client socket
     * 
     * Returns the number of bytes sent.
     * 
     * @param  WebRequest $request The http request
     * @param  int $code The http status code to sent
     * @param  string $message The http status message to send
     * @return int
     */
    protected function sendResponseHeadersToClient(WebRequest $request, $code, $message)
    {
        $headers = [
            "Date"       => gmdate("D, d M Y H:i:s T"),
            "Connection" => "close"
        ];
        
        $bytes = $this->sendToClient($request->getVersion() . " {$code} {$message}\r\n");
        foreach($headers as $header => $value) {
            $bytes += $this->sendToClient("{$header}: {$value}\r\n");
        }
        $bytes += $this->sendToClient("\r\n");
        
        return $bytes;
    }

    /**
     * Sends a string through the client socket
     * 
     * Returns the number of bytes sent.
     * 
     * @param  string $line The string to send
     * @return int
     * @throws Exceptions\HttpStatusError
     */
    protected function sendToClient($line)
    {
        $bytes = 0;
        $len   = strlen($line);
        if ($len) {
            while(true) {
                $sent = socket_write($this->client, $line, $len);
                if (false === $sent) {
                    throw new Exceptions\HttpStatusError(
                        "Internal server error.",
                        500
                    );
                }

                $bytes += $sent;
                if ($sent < $len) {
                    $line = substr($line, $sent);
                    $len -= $sent;
                } else {
                    break;
                }
            }
        }

        return $bytes;
    }

    /**
     * Returns the data for the requested file
     * 
     * @param  WebRequest $request The http request
     * @return string
     * @throws Exceptions\HttpStatusError
     */
    protected function getFile(WebRequest $request)
    {
        $path = realpath($this->dirRoot . DIRECTORY_SEPARATOR . $request->getPath());
        if (false === $path) {
            throw new Exceptions\HttpStatusError(
                "File not found.",
                404
            );
        }
        if (is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR . $this->index;
        }
        
        $contents = null;
        $info     = pathinfo($path);
        if ($info["extension"] == "php") {
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include($path);
            $contents = ob_get_contents();
            ob_end_clean();
        } else {
            $contents = file_get_contents($path);
        }
        
        return $contents;
    }
} 