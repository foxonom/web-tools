<?php
namespace Headzoo\Web\Tools;

interface WebServerInterface
{
    /**
     * The default host/ip to bind to
     */
    const DEFAULT_HOST = "127.0.0.1";
    
    /**
     * The default port to listen on
     */
    const DEFAULT_PORT = 8888;

    /**
     * Name of the default index file
     */
    const DEFAULT_INDEX = "index.html";

    /**
     * Starts the http server
     *
     * @param  bool $single Handle a single request and shutdown
     * @throws Exceptions\SocketException
     */
    public function start($single = true);
} 