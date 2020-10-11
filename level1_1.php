<?php
echo "level_1_1<br>";
echo (2 + 1) . "<br>";

echo "level_1_2<br>";
// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
function readHttpLikeInput()
{
    $f = fopen('php://stdin', 'r');
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
        "body" => ltrim($arr[array_search("\r", $arr) + 1]),
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


$test = "GET /sum?nums=1,2,3 HTTP/1.1
Host: student.shpp.me";

$http = parseTcpStringAsHttpRequest($test);
//$http = parseTcpStringAsHttpRequest($contents);

echo(json_encode($http, JSON_PRETTY_PRINT));

function processHttpRequest($method, $uri, $headers, $body)
{
    $statuscode = getStatusCode($method, $uri);
    $statusmessage = getStatusMessage($statuscode);
    $body_response = getBody($uri);
    outputHttpResponse($statuscode, $statusmessage, $headers, $body_response);
}

function getStatusCode($method, $uri)
{
    if ($method == "GET" &&
        preg_match_all("~^\/sum\?nums=(\d+(\,(\d)+)*)$~", // test in https://regex101.com/
            $uri, $out, PREG_PATTERN_ORDER)) {
        return "200";
//        $body_response = array_sum(explode(",", $out[1][0]));
    }
}

function getStatusMessage($statuscode)
{
    switch ($statuscode) {
        case "404":
            return "Not found";
            break;
        case "200":
            return "Ok";
            break;
        default:
            return "";
    }
}

function outputHttpResponse($statuscode, $statusmessage, $headers, $body)
{
    echo "<br> HTTP/1.1 " . "$statuscode" . " " . "$tatusmessage" . "$statusmessage<br>";
    echo date('l jS \of F Y h:i:s A') . "<br>";
    foreach ($headers as $k => $v) {
        echo "$k" . ": " . "$v<br>";
    }
    echo $body != null ? "<br> $body" : "";
}

processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);