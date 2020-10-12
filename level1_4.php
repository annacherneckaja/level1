<?php

function readHttpLikeInput()
{
    $f = fopen('stdin4', 'r');
    $store = "";
    $toread = 0;
    while ($line = fgets($f)) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/', $line, $m))
            $toread = $m[1] * 1;
        if ($line == "\r\n")
            break;
    }
    if ($toread > 0)
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

function parseTcpStringAsHttpRequest($string)
{
    $arr = explode("\n", $string);
    return array(
        "method" => getMethod($arr[0]),
        "uri" => getURI($arr[0]),
        "headers" => getHeaders($arr),
        "body" => ltrim($arr[array_search("", $arr) + 1]),
    );
}

function getMethod($string)
{
    $requestMethods = "/GET|HEAD|POST|PUT|DELETE|CONNECT|OPTIONS|TRACE|PATCH/";
    preg_match($requestMethods, $string, $match);
    return $match[0];
}

function getURI($string)
{
// https://docs.microsoft.com/en-us/previous-versions/msp-n-p/ff650303(v=pandp.10)?redirectedfrom=MSDN
    $URI_Syntax = '~\/[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)*)~'; //https://mathiasbynens.be/demo/url-regex
    preg_match($URI_Syntax, $string, $match);
    return $match[0];
}

function getHeaders($array)
{
    $headers = array();
    for ($i = 1; $i < count($array); $i++) {
        if ($array[$i] == "\r") break;
        if (preg_match("/:/", $array[$i])) {
            $header_name = explode(":", $array[$i])[0];
            $header_value = explode(":", $array[$i])[1];
            $headers[$header_name] = trim($header_value);
        }
    }
    return $headers;
}

$http = parseTcpStringAsHttpRequest($contents);
//$http = parseTcpStringAsHttpRequest($contents);

echo(json_encode($http, JSON_PRETTY_PRINT));

function processHttpRequest($method, $uri, $headers, $body)
{
    $body_response = "";
    if ($method == "POST") {
        if (preg_match("~^/api/checkLoginAndPassword$~", $uri) &&
            $headers["Content-Type"] == "application/x-www-form-urlencoded") {
            if (!include 'passwords.txt') {
                $status_code = 500;
            } elseif (checkLoginAndPassword($body)) {
                $status_code = 200;
                $body_response = "FOUND";
            } else {
                $status_code = 403;
                $body_response = "incorrect login or password";
            }
        } else $status_code = 404;
    } else $status_code = 400;
    include 'statusMessage';
    $headers_response = getHeadersResponse($body_response);

    outputHttpResponse($status_code,$status_massage_array[$status_code], $headers_response, $body_response);
}

function checkLoginAndPassword($body)
{
    $parsed_body = preg_split("~[&=]~", $body);
    if (count($parsed_body) != 4) return false;
    $login_and_password = "~" . $parsed_body[1] . ":" . $parsed_body[3] . "~";
    return preg_match($login_and_password, file_get_contents('passwords.txt'));
}

function getHeadersResponse($body_response)
{
    return array(
        "Server" => "Apache / 2.2.14 (Win32)",
        "Content - Length" => strlen(json_encode($body_response)),
        "Connection" => "Closed",
        "Content - Type" => "text / html;charset = utf - 8",
    );
}

function outputHttpResponse($status_code, $status_message, $headers, $body)
{
    echo "<br> HTTP/1.1 " . "$status_code" . " " . "$status_message<br>";
    echo date('l jS \of F Y h:i:s A') . "<br>";
    foreach ($headers as $k => $v) {
        echo "$k" . ": " . "$v<br>";
    }
    echo $body;
}

processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);