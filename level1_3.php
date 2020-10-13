<?php

function readHttpLikeInput()
{
    $f = fopen('stdin3', 'r');
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

/**
 * Searches subject for a match to the regular expression given in pattern.
 * @param $string - the input string;
 * @return int
 */
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

function processHttpRequest($method, $uri, $headers, $body)
{
    if ($method == "GET") {
        if (preg_match("~^/sum~", $uri)) {
            if (preg_match_all("~^/sum\?nums=(\d+(,(\d)+)*)$~",
                $uri, $out, PREG_PATTERN_ORDER)) {
                $status_code = 200;
                $body_response = array_sum(explode(",", $out[1][0]));
            } else {
                $status_code = 400;
            }
        } else $status_code = 404;
    } else $status_code = 400;
    $status_message = getStatusMessage($status_code);
    $headers_response = getHeadersResponse($body_response);

    outputHttpResponse($status_code, $status_message, $headers_response, $body_response);
}

function getHeadersResponse($body_response)
{
    return array(
        "Server" => "Apache/2.2.14 (Win32)",
        "Connection" => "Closed",
        "Content-Type" => "text/html; charset=utf-8",
        "Content-Length" => $body_response != NULL ? strlen(json_encode($body_response)) : 0
    );
}

function getStatusMessage($status_code)
{
    switch ($status_code) {
        case 400:
            return "Bad Request";
        case 404:
            return "Not found";
        case 200:
            return "OK";
        default:
            return "";
    }
}

function outputHttpResponse($status_code, $status_message, $headers, $body)
{
    echo "HTTP/1.1 " . "$status_code" . " " . "$status_message\n";
    echo date('l jS \of F Y h:i:s A') . "\n";
    foreach ($headers as $k => $v) {
        echo "$k" . ": " . "$v\n";
    }
    echo $body != null ? "$body" : "";
}

processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);