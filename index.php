<?php
 // Global database/session initialization.
 require_once "./config.php";
 
  // If we're already logged in, redirect back to the homepage.
 if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
  header("location: ./bookmarks.php?folder=1");
  exit;
 } else {
  // header("location: ./login.php?return=$_SERVER['REQUEST_URI']");
  header("location: ./login.php");
  exit;
}

?>
