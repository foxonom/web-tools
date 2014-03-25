<?php
/**
 * Web page for unit testing. This page echos back the $_SERVER, $_GET, and $_POST values as a json encoded
 * string.
 */
header("X-Test-Response: Howdy");
foreach($_SERVER as $key => $value) {
    if (substr($key, 0, 5) == "HTTP_") {
        $key = str_replace("HTTP_", "", $key);
        $key = str_replace("_", " ", $key);
        $key = ucwords(strtolower($key));
        $key = str_replace(" ", "-", $key);
        header("{$key}: {$value}");
    }
}
$_SERVER["GET"]  = $_GET;
$_SERVER["POST"] = $_POST;
echo json_encode($_SERVER, JSON_PRETTY_PRINT);