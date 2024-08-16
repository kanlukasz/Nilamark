<?php
 // Global database/session initialization.
 require_once "./config.php";

 // If we're already logged out, redirect back to the homepage.
 if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: ./index.php");
  exit;
 }

 echo("Logging out...");

 session_unset();
 session_destroy();

 redirect("./index.php");
?>
