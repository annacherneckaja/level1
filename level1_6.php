
<?php

if($_POST) {
    if (file_exists(PASSWORDS)) {
        if (processIdentifying()) {
            echo "<h1 style=\"color:green\">FOUND</h1>";
        } else {
            header("HTTP/1.1 404 Not Found");
            echo "<h1 style=\"color:red\">NOT FOUND</h1>";
        }
    } else {
        header("HTTP/1.1 500 Internal Server Error ");
        echo '';
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo '';
}

function processIdentifying()
{
    $logNPassword = $_POST['login'] . ':' . $_POST['password'];
    $passwords = preg_split("/\r?\n/", file_get_contents(PASSWORDS));
    foreach ($passwords as $password) {
        if ($logNPassword == $password) {
            return true;
        }
    }
    return false;
}