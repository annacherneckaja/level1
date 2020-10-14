<?php
// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
function readHttpLikeInput()
{
    $f = fopen('test2', 'r');
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

echo json_encode($http, JSON_PRETTY_PRINT);
