<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <title>Level1_6</title>
</head>
<body>

<?php require "blocks/sum.php"?>

</body>
</html>

<?php
include 'statusMessage';
function getHeadersResponse($body_response)
{
    return array(
        "Server" => "Apache/2.2.14 (Win32)",
        "Content-Length" => strlen(json_encode($body_response)),
        "Connection" => "Closed",
        "Content-Type" => "text/html; charset=utf-8",
    );
}
if ($_GET) {
    if (!key_exists('nums', $_GET)) {
        $status_code = 400;
    } else {
        foreach ($_GET['nums'] as $num) {
            if (preg_match("~\D~", $num)) $status_code = 400;
        }
    }
    $status_code = $status_code == 400 ? 400 : 200;
    $body = $status_code == 200 ? array_sum($_GET['nums']) : "";
    $headers = getHeadersResponse($body);

    echo "HTTP/1.1 " . "$status_code" . " " . "$status_massage_array[$status_code]\r\n";
    echo date('l jS \of F Y h:i:s A') . "\r\n";
    foreach ($headers as $k => $v) {
        echo "$k" . ": " . "$v\r\n";
    }
    echo "<h1 align=\"center\" style=\"color:blue\">$body</h1>";
}
?>

<?php require "blocks/auth.php"?>

<?php
if ($_POST) {
    $login_and_password = "~" . $_POST['login'] . ":" . $_POST['password'] . "~";

    if (!preg_match($login_and_password, file_get_contents('assets/passwords.txt'))) {
        $status_code = 400;
        $body = 'INCORRECT LOGIN OR PASSWORD';
    } else {
        $status_code = 200;
        $body = 'FOUND';
    }

    $headers = getHeadersResponse($body);

    echo "HTTP/1.1 " . "$status_code" . " " . "$status_massage_array[$status_code]\r\n";
    echo date('l jS \of F Y h:i:s A') . "\r\n";
    foreach ($headers as $k => $v) {
        echo "$k" . ": " . "$v\r\n";
    }
    echo $status_code == 200 ? "<h1 style=\"color:green\">$body</h1>" :
        "<h1 style=\"color:red\">$body</h1>";
}
?>