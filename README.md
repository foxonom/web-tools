Web Tools
========= 
A collection of PHP classes used for making web requests, and parsing information related to the HTTP protocol.

- [Overview](#overview)
- [Requirements](#requirements)
- [Installing](#installing)
- [Quick Start](#quick-start)
- [Class Documentation](#class-documentation)
- [Change Log](#change-log)
- [TODO](#todo)
- [License](#license)


Overview
--------
This library contains classes which can be used to perform the following tasks:

* Make GET/POST requests to remote servers.
* Parse raw HTTP headers, and build raw HTTP headers.
* Parse raw HTTP requests.
* Provides a very small web server written in PHP for testing purposes.


Requirements
------------
* [PHP 5.5 or greater](https://php.net/downloads.php).
* [cURL PHP extension](https://php.net/curl).
* [psr/log](https://github.com/php-fig/log).
* [headzoo/utilities](https://github.com/headzoo/utilities).


Installing
----------
The library may be installed using either git or Composer, but I strongly recommend using Composer so dependencies
will be automatically installed. Add the web-tools dependency to your composer.json using the following code:

```
"require": {
    "headzoo/web-tools" : "dev-master"
}
```


Quick Start
-----------

```php
<?php
use Headzoo\Web\Tools\WebClient;
use Headzoo\Web\Tools\HttpMethods;

// Make a simple GET request.
$web = new WebClient();
$response = $web->get("http://headzoo.io");

// Make a simple POST request.
$web = new WebClient();
$response = $web->post("http://headzoo.io", ["arg1" => "value1"]);

// The response is an instance of WebResponse, which provides the response information.
echo $response->getCode();
echo $response->getBody();
print_r($response->getHeaders());

// Making a requests with more configuration.
$web = new WebClient(HttpMethods::GET);
$web
    ->addHeader("Content-Type", "application/json")
    ->setUserAgent("My-Web-Client")
    ->setBasicAuth("headzoo", "password");
$response = $web->request("http://headzoo.io");
```


Class Documentation
-------------------
This readme only briefly discussing some of the important classes in the library. See the class source
code for more information.

### Headzoo\Web\Tools\WebClient
Used to make any kind of HTTP request, including GET, POST, PUT, and DELETE.

### Headzoo\Web\Tools\WebResponse
Represents a server response from a HTTP request.

### Headzoo\Web\Tools\WebServer
A small, not yet finished testing web server.

### Headzoo\Web\Tools\WebRequest
Represents an incoming web request.

### Headzoo\Web\Tools\Builders\Headers
Normalizes and builds raw HTTP headers.

### Headzoo\Web\Tools\Parsers\Headers
Parses raw HTTP headers into an array of key/value pairs.

### Headzoo\Web\Tools\Parsers\Request
Parses a raw HTTP request into body, headers, etc.

### Headzoo\Web\Tools\HttpMethods
Class of constants representing the supported request methods.

### Headzoo\Web\Tools\Utils
Contains various utility methods used through out the library.


Change Log
----------
##### v0.2 - 2013-12-31
* Major overhaul.

##### v0.1 - 2013-12-18
* Released code under MIT license.


TODO
----
* Add cookie management.
* Add certificate management.


License
-------
This content is released under the MIT License. See the included LICENSE for more information.