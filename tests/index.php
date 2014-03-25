<?php
/**
 * Web page for unit testing. This page echos back the $_SERVER, $_GET, and $_POST values as a json encoded
 * string.
 */
header("X-Testing-Pong: " . $_SERVER["HTTP_X_TESTING_PING"]);
$_SERVER["GET"]  = $_GET;
$_SERVER["POST"] = $_POST;
echo json_encode($_SERVER, JSON_PRETTY_PRINT);