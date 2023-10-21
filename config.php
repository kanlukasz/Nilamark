<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

$sqlservername = "";
$sqldbname = "";
$sqlusername = "";
$sqlpassword = "";

try {
    $pdo = new PDO("mysql:host=$sqlservername;dbname=$sqldbname", $sqlusername, $sqlpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

function redirect($url) {
    $redirect = '<script type="text/javascript">' . 'window.location = "' . $url . '"' . '</script>';
    echo $redirect;
}

?>
