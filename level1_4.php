<?php

function readHttpLikeInput()
{
    $f = fopen('stdin4.txt', 'r');
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
    $lines = explode("\n", $string);
    $start_line = explode(" ", $lines[0]);
    return array(
        "method" => $start_line[0],
        "uri" => $start_line[1],
        "headers" => getHeaders($lines),
        "body" => ltrim($lines[array_search("", $lines) + 1]),
    );
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

function processHttpRequest($method, $uri, $headers, $body)
{
    $body_response = "";
    if ($method == "POST") {
        if (preg_match("~^/api/checkLoginAndPassword$~", $uri) &&
            $headers["Content-Type"] == "application/x-www-form-urlencoded") {
            if (!file_exists('assets/passwords.txt')) {
                $status_code = 500;
            } elseif (checkLoginAndPassword($body)) {
                $status_code = 200;
                $body_response = "FOUND";
            } else {
                $status_code = 403;
                $body_response = "INCORRECT LOGIN OR PASSWORD";
            }
        } else $status_code = 404;
    } else $status_code = 400;
    include 'statusMessage';
    $headers_response = getHeadersResponse($body_response);

    outputHttpResponse($status_code, $status_massage_array[$status_code], $headers_response, $body_response);
}

function checkLoginAndPassword($body)
{
    parse_str($body, $parsed_body);
    $login_and_password = "~" . $parsed_body['login'] . ":" . $parsed_body['password'] . "~";
    return preg_match($login_and_password, file_get_contents('assets/passwords.txt'));
}

function getHeadersResponse($body_response)
{
    return array(
        "Server" => "Apache/2.2.14 (Win32)",
        "Content-Length" => strlen(json_encode($body_response)),
        "Connection" => "Closed",
        "Content-Type" => "text/html; charset=utf-8",
    );
}

function outputHttpResponse($status_code, $status_message, $headers, $body)
{
    echo "HTTP/1.1 " . "$status_code" . " " . "$status_message\r\n";
    echo date('l jS \of F Y h:i:s A') . "\r\n";
    foreach ($headers as $k => $v) {
        echo "$k" . ": " . "$v\r\n";
    }
    echo $status_code == 200 ? "<h1 style=\"color:green\">$body</h1>" :
        "<h1 style=\"color:red\">$body</h1>";

}

processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);