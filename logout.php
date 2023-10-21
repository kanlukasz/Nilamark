<?php
 // Global database/session initialization.
 require_once "./config.php";
 require_once "./cookies.php";

 // If we're already logged out, redirect back to the homepage.
 if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: ./index.php");
  exit;
 }

 echo("Logging out...");

 session_unset();
 session_destroy();

 if (isset($_COOKIE['remember_me'])) {
  $token = filter_input(INPUT_COOKIE, 'remember_me', FILTER_SANITIZE_STRING);
  delete_token($token, $pdo);
  unset($_COOKIE['remember_me']);
  setcookie('remember_me', '', 1);
 }

 redirect("./index.php");
?>
